<?php

namespace App\Controller\Admin;

use App\Lib\ImageArchive;
use App\Lib\LearningWorkspace;
use App\Lib\WorkspaceContent;
use App\Lib\WorkspaceOrder;
use App\Lib\WorkspacePresentation;
use App\Lib\WorkspaceStructure;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\Log\Log;
use Cake\Routing\Router;
use Cake\Utility\Security;

class WorkspaceController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        if (!$this->request->getSession()->check('LearningWorkspace.token')) {
            $this->request->getSession()->write('LearningWorkspace.token', Security::randomString(64));
        }
        if (!in_array($this->request->getMethod(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            $expected = $this->request->getSession()->read('LearningWorkspace.token');
            if (!hash_equals($expected, $this->request->getHeaderLine('X-Workspace-Token'))) {
                throw new ForbiddenException('Your editing session has expired. Reload the workspace.');
            }
        }
    }

    public function index(): Response
    {
        $this->request->allowMethod(['get']);
        $file = WWW_ROOT . 'learning-admin/index.html';
        if (!is_file($file)) {
            return $this->response->withStatus(503)->withType('text')->withStringBody(
                'Build the Angular admin first: cd core/frontend && npm run build:admin'
            );
        }
        $html = file_get_contents($file);
        $base = Router::url('/learning-admin/');
        $html = preg_replace('#<base href="[^"]*">#', '<base href="' . h($base) . '">', $html);
        $html = preg_replace('#<title>[^<]*</title>#', '<title>' . h($this->studioName()) . '</title>', $html);
        $config = json_encode([
            'api' => Router::url(['controller' => 'Workspace', 'action' => 'bootstrap', 'prefix' => 'Admin']),
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
        $html = str_replace('</head>', '<script id="workspace-config" type="application/json">'
            . $config . '</script></head>', $html);

        return $this->response->withType('html')->withHeader('Cache-Control', 'no-store')->withStringBody($html);
    }

    public function bootstrap(): Response
    {
        $this->request->allowMethod(['get']);
        return $this->json([
            'token' => $this->request->getSession()->read('LearningWorkspace.token'),
            'types' => LearningWorkspace::TYPES,
            'exerciseTypes' => \App\Lib\ExerciseContent::TYPES,
            'language' => Configure::read('LANGUAGE'),
            'appName' => $this->studioName(),
            'legacy' => Router::url('/admin/learning-path/manage-paths'),
            'userId' => $this->getAuthUser()->id,
        ]);
    }

    private function studioName(): string
    {
        return trim((string)(Configure::read('App.name') ?: 'Learning')) . ' Studio';
    }

    public function records(string $type): Response
    {
        $this->request->allowMethod(['get']);
        $definition = LearningWorkspace::definition($type);
        $table = $this->getTableLocator()->get($definition['table']);
        $query = $table->find();
        $search = mb_substr(trim((string)$this->request->getQuery('q', '')), 0, 200);
        if ($search !== '') {
            $conditions = [];
            foreach ($definition['fields'] as $field) {
                if (isset($definition['references'][$field])) {
                    continue;
                }
                $conditions[$table->aliasField($field) . ' LIKE'] = '%' . $search . '%';
            }
            if (ctype_digit($search)) {
                $conditions[$table->aliasField('id')] = (int)$search;
            }
            $query->where(['OR' => $conditions]);
        }
        $parentType = (string)$this->request->getQuery('parentType', '');
        $parentId = (int)$this->request->getQuery('parentId');
        $pathId = (int)$this->request->getQuery('pathId') ?: null;
        if ($parentType !== '') {
            $this->restrictRelation($query, $parentType, $parentId, $type);
        }
        $sort = (string)$this->request->getQuery('sort', $definition['title']);
        if (!in_array($sort, ['id', 'sequence', $definition['title']], true)) {
            throw new BadRequestException('Invalid sort column.');
        }
        $scope = $parentType !== '' ? WorkspaceOrder::scopeFor($parentType, $type) : null;
        if ($scope === 'level-units' && !$pathId) {
            $scope = null;
        }
        $order = null;
        $pageSize = 25;
        if ($sort === 'sequence' && $scope) {
            (new WorkspaceOrder($this->getTableLocator()))->orderRecords($query, $scope, $parentId, $pathId);
            $order = ['scope' => $scope, 'parentId' => $parentId, 'pathId' => $pathId];
            $pageSize = $search === '' ? WorkspaceOrder::PAGE_SIZE : 25;
        } else {
            $sort = $sort === 'sequence' ? $definition['title'] : $sort;
            $direction = $this->request->getQuery('direction') === 'desc' ? 'DESC' : 'ASC';
            $query->order([$table->aliasField($sort) => $direction, $table->aliasField('id') => 'ASC']);
        }
        $page = max(1, (int)$this->request->getQuery('page', 1));
        $total = $query->count();
        $rows = $query->limit($pageSize)->offset(($page - 1) * $pageSize)->all();
        $items = array_map(function ($row) use ($type, $order) {
            $item = $this->summary($type, $row);
            if ($order) {
                $item['linkId'] = (int)$row->get('link_id');
            }
            return $item;
        }, $rows->toList());

        return $this->json([
            'items' => (new WorkspacePresentation($this->getTableLocator()))->decorate($items),
            'total' => $total, 'page' => $page, 'pageSize' => $pageSize,
            'order' => $order && $search === '' && $total <= $pageSize ? $order : null,
        ]);
    }

    public function search(): Response
    {
        $this->request->allowMethod(['get']);
        $q = mb_substr(trim((string)$this->request->getQuery('q', '')), 0, 200);
        if (mb_strlen($q) < 2) {
            throw new BadRequestException('Enter at least two characters.');
        }
        $items = [];
        foreach (LearningWorkspace::TYPES as $type => $definition) {
            $table = $this->getTableLocator()->get($definition['table']);
            $or = [$definition['title'] . ' LIKE' => '%' . $q . '%'];
            if (ctype_digit($q)) {
                $or['id'] = (int)$q;
            }
            foreach ($table->find()->where(['OR' => $or])->order(['id' => 'ASC'])->limit(5) as $row) {
                $items[] = $this->summary($type, $row);
            }
        }

        return $this->json(['items' => (new WorkspacePresentation($this->getTableLocator()))->decorate($items)]);
    }

    public function activities(string $unitId): Response
    {
        $this->request->allowMethod(['get']);
        $this->getUnitsTable()->get($unitId);
        $query = $this->getUnitdetailsTable()->find()->contain(['Lessons', 'Exercises'])
            ->where(['Unitdetails.unit_id' => $unitId]);
        $q = mb_substr(trim((string)$this->request->getQuery('q', '')), 0, 200);
        if ($q !== '') {
            $conditions = ['Lessons.name LIKE' => '%' . $q . '%', 'Exercises.name LIKE' => '%' . $q . '%'];
            if (ctype_digit($q)) {
                $conditions['Lessons.id'] = (int)$q;
                $conditions['Exercises.id'] = (int)$q;
            }
            $query->where(['OR' => $conditions]);
        }
        $total = $query->count();
        $page = max(1, (int)$this->request->getQuery('page', 1));
        $pageSize = $q === '' ? WorkspaceOrder::PAGE_SIZE : 25;
        $items = [];
        $complete = true;
        foreach (
            $query->order(['Unitdetails.sequence' => 'ASC', 'Unitdetails.id' => 'ASC'])
            ->limit($pageSize)->offset(($page - 1) * $pageSize) as $activity
        ) {
            if ($activity->lesson) {
                $items[] = ['linkId' => (int)$activity->id] + $this->summary('lessons', $activity->lesson);
            } elseif ($activity->exercise) {
                $items[] = ['linkId' => (int)$activity->id] + $this->summary('exercises', $activity->exercise);
            } else {
                // A link to a deleted activity cannot be shown, so the visible list is not the full order.
                $complete = false;
            }
        }
        $order = $q === '' && $complete && $total <= $pageSize
            ? ['scope' => 'unit-activities', 'parentId' => (int)$unitId, 'pathId' => null] : null;
        return $this->json(['items' => (new WorkspacePresentation($this->getTableLocator()))->decorate($items),
            'page' => $page, 'pageSize' => $pageSize, 'total' => $total, 'order' => $order]);
    }

    public function exerciseContent(string $id): Response
    {
        $this->request->allowMethod(['get', 'post']);
        $service = new WorkspaceContent($this->getTableLocator());
        try {
            $content = $this->request->is('post')
                ? $service->saveExercise((int)$id, $this->request->getData())
                : $service->exercise((int)$id);
            return $this->json($this->enrichContent($content));
        } catch (\Cake\Http\Exception\ConflictException $error) {
            return $this->json(['message' => $error->getMessage()], 409);
        } catch (\InvalidArgumentException $error) {
            return $this->json(['message' => $error->getMessage()], 422);
        } catch (\Cake\ORM\Exception\PersistenceFailedException $error) {
            return $this->json(['message' => 'Content could not be saved.', 'errors' => $error->getEntity()->getErrors()], 422);
        }
    }

    public function lessonFrames(string $id): Response
    {
        $this->request->allowMethod(['get']);
        return $this->json((new WorkspaceContent($this->getTableLocator()))
            ->frames((int)$id, max(1, (int)$this->request->getQuery('page', 1))));
    }

    public function lessonFrame(string $lessonId, ?string $frameId = null): Response
    {
        $this->request->allowMethod(['get', 'post']);
        $service = new WorkspaceContent($this->getTableLocator());
        try {
            if (!$frameId && !$this->request->is('post')) {
                throw new BadRequestException('Select a frame.');
            }
            $content = $this->request->is('post')
                ? $service->saveFrame((int)$lessonId, $frameId ? (int)$frameId : null, $this->request->getData())
                : $service->frame((int)$lessonId, (int)$frameId);
            return $this->json($this->enrichContent($content));
        } catch (\Cake\Http\Exception\ConflictException $error) {
            return $this->json(['message' => $error->getMessage()], 409);
        } catch (\InvalidArgumentException $error) {
            return $this->json(['message' => $error->getMessage()], 422);
        } catch (\Cake\ORM\Exception\PersistenceFailedException $error) {
            return $this->json(['message' => 'Frame could not be saved.', 'errors' => $error->getEntity()->getErrors()], 422);
        }
    }

    public function reorder(): Response
    {
        $this->request->allowMethod(['post']);
        $scope = (string)$this->request->getData('scope', '');
        $parentId = (int)$this->request->getData('parentId');
        $pathId = (int)$this->request->getData('pathId') ?: null;
        $ids = $this->request->getData('ids');
        try {
            $ids = (new WorkspaceOrder($this->getTableLocator()))->reorder($scope, $parentId, $pathId, $ids);
        } catch (\Cake\Http\Exception\ConflictException $error) {
            return $this->json(['message' => $error->getMessage()], 409);
        } catch (\InvalidArgumentException $error) {
            return $this->json(['message' => $error->getMessage()], 422);
        } catch (\Cake\ORM\Exception\PersistenceFailedException $error) {
            return $this->json(['message' => 'The new order could not be saved.'], 422);
        }
        Log::info('Learning workspace reorder: ' . json_encode([
            'user' => $this->getAuthUser()->id, 'scope' => $scope, 'parent' => $parentId,
            'path' => $pathId, 'ids' => $ids,
        ]));
        return $this->json(['ids' => $ids]);
    }

    public function create(string $type): Response
    {
        $this->request->allowMethod(['post']);
        $parent = $this->request->getData('parent');
        return $this->structure('create', ['type' => $type], function (WorkspaceStructure $structure) use ($type, $parent) {
            $fields = $this->request->getData('fields');
            return $structure->create($type, is_array($fields) ? $fields : [], is_array($parent) ? $parent : null);
        });
    }

    public function link(string $unitId): Response
    {
        $this->request->allowMethod(['post']);
        return $this->structure('link', ['unit' => (int)$unitId], function (WorkspaceStructure $structure) use ($unitId) {
            $pathId = (int)$this->request->getData('pathId') ?: null;
            return ['linkIds' => $structure->link((int)$unitId, $pathId, $this->request->getData('items'))];
        });
    }

    public function unlink(string $unitId, string $linkId): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        return $this->structure('unlink', ['unit' => (int)$unitId, 'link' => (int)$linkId], function (WorkspaceStructure $structure) use ($unitId, $linkId) {
            $structure->unlink((int)$unitId, (int)$linkId);
            return ['removed' => (int)$linkId];
        });
    }

    public function copyPlan(string $type, string $id): Response
    {
        $this->request->allowMethod(['get']);
        try {
            return $this->json((new WorkspaceStructure($this->getTableLocator()))->plan($type, (int)$id));
        } catch (\InvalidArgumentException $error) {
            return $this->json(['message' => $error->getMessage()], 422);
        }
    }

    public function copy(string $type, string $id): Response
    {
        $this->request->allowMethod(['post']);
        return $this->structure('copy', ['type' => $type, 'source' => (int)$id], function (WorkspaceStructure $structure) use ($type, $id) {
            $options = $this->request->getData();
            return $structure->copy($type, (int)$id, is_array($options) ? $options : []);
        });
    }

    private function structure(string $action, array $audit, callable $callback): Response
    {
        try {
            $result = $callback(new WorkspaceStructure($this->getTableLocator()));
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $error) {
            return $this->json(['message' => 'That record no longer exists. Refresh and try again.'], 404);
        } catch (\InvalidArgumentException $error) {
            return $this->json(['message' => $error->getMessage()], 422);
        } catch (\Cake\ORM\Exception\PersistenceFailedException $error) {
            $errors = $error->getEntity()->getErrors();
            $first = $errors ? current(array_merge(...array_values(array_map('array_values', $errors)))) : null;
            return $this->json(['message' => is_string($first) ? $first : 'The change could not be saved.'], 422);
        }
        Log::info('Learning workspace ' . $action . ': ' . json_encode($audit + [
            'user' => $this->getAuthUser()->id, 'result' => $result,
        ]));
        return $this->json($result);
    }

    private function enrichContent(array $content): array
    {
        $cards = [];
        $files = [];
        $rows = $content['options'] ?? $content['blocks'] ?? [];
        $rows[] = $content['fields'];
        foreach ($rows as $row) {
            $row = ($row['fields'] ?? $row) + ['custom' => $row['custom'] ?? []];
            foreach (['card_id', 'responce_card_id'] as $field) {
                if (!empty($row[$field])) {
                    $cards[] = $row[$field];
                }
            }
            foreach ($row + ($row['custom'] ?? []) as $field => $value) {
                if (preg_match('/(?:audio|image|video)_id$/', $field) && $value) {
                    $files[] = $value;
                }
            }
        }
        $presentation = new WorkspacePresentation($this->getTableLocator());
        $content['cards'] = $presentation->cards($cards);
        $content['files'] = $presentation->files($files);
        return $content;
    }

    public function record(string $type, string $id): Response
    {
        $this->request->allowMethod(['get']);
        LearningWorkspace::definition($type);
        return $this->json($this->detail($type, $id));
    }

    public function save(string $type, ?string $id = null): Response
    {
        $this->request->allowMethod(['post']);
        $definition = LearningWorkspace::definition($type);
        if ($id === null && !$definition['create']) {
            throw new BadRequestException('Create this record in the advanced editor to initialize its structure.');
        }
        $data = $this->request->getData('fields');
        if (!is_array($data)) {
            throw new BadRequestException('A fields object is required.');
        }
        $allowed = array_merge($definition['fields'], array_keys($definition['media']));
        if (array_diff(array_keys($data), $allowed)) {
            throw new BadRequestException('The request contains read-only or unknown fields.');
        }
        foreach ($data as $value) {
            if ($value !== null && !is_scalar($value)) {
                throw new BadRequestException('Field values must be scalar.');
            }
        }
        foreach ($definition['required'] as $field) {
            if (($id === null || array_key_exists($field, $data)) && trim((string)($data[$field] ?? '')) === '') {
                return $this->json(['message' => 'Required fields cannot be blank.', 'errors' => [
                    $field => ['required' => 'This field is required.'],
                ]], 422);
            }
        }
        foreach ($definition['media'] as $field => $mediaType) {
            if (isset($data[$field])) {
                $file = $this->getFilesTable()->get($data[$field]);
                if ($file->type !== $mediaType) {
                    throw new BadRequestException('The selected media has the wrong type.');
                }
            }
        }
        foreach ($definition['options'] ?? [] as $field => $options) {
            if (array_key_exists($field, $data) && !in_array($data[$field], $options, true)) {
                return $this->json(['message' => 'Choose one of the listed values.', 'errors' => [
                    $field => ['inList' => 'Choose one of: ' . implode(', ', $options) . '.'],
                ]], 422);
            }
        }
        foreach ($definition['references'] ?? [] as $field => $reference) {
            if (isset($data[$field])) {
                $this->getTableLocator()->get($reference === 'card-types' ? 'Cardtype' : 'Cardgrouptype')
                    ->get($data[$field]);
            }
        }
        $initialCard = null;
        if ($type === 'groups' && $id === null) {
            $initialCard = $this->getCardTable()->get($this->request->getData('initialCardId'));
        }
        $table = $this->getTableLocator()->get($definition['table']);
        return $table->getConnection()->transactional(function () use ($table, $type, $id, $data, $initialCard, $definition) {
            // Lock the row so two clients cannot both pass the same revision check.
            $entity = $id === null ? $table->newEmptyEntity() : $this->lockedRecord($table, $id);
            if (
                $id !== null && !hash_equals(
                    $this->revision($type, $entity),
                    (string)$this->request->getData('revision')
                )
            ) {
                return $this->json(['message' => 'This record changed in another workspace. Reload before saving.'], 409);
            }
            $patch = $id === null ? $data + ($definition['defaults'] ?? []) : ['id' => $entity->id] + $data;
            $table->patchEntity($entity, $patch, ['fields' => array_keys($patch)]);
            if (!$table->save($entity)) {
                return $this->json([
                    'message' => 'The record could not be saved.', 'errors' => $entity->getErrors(),
                ], 422);
            }
            if ($initialCard) {
                $links = $this->getCardcardgroupTable();
                $links->saveOrFail($links->newEntity(['card_group_id' => $entity->id, 'card_id' => $initialCard->id]));
            }
            Log::info('Learning workspace save: ' . json_encode([
                'type' => $type, 'id' => $entity->id, 'fields' => array_keys($data),
                'user' => $this->getAuthUser()->id,
            ]));

            return $this->json($this->detail($type, (string)$entity->id));
        });
    }

    public function choices(string $reference): Response
    {
        $this->request->allowMethod(['get']);
        $tables = ['card-types' => 'Cardtype', 'group-types' => 'Cardgrouptype'];
        if (!isset($tables[$reference])) {
            throw new NotFoundException('Unknown reference type.');
        }
        $q = mb_substr(trim((string)$this->request->getQuery('q', '')), 0, 200);
        $query = $this->getTableLocator()->get($tables[$reference])->find();
        if ($this->request->getQuery('id')) {
            $query->where(['id' => (int)$this->request->getQuery('id')]);
        } elseif ($q !== '') {
            $query->where(['title LIKE' => '%' . $q . '%']);
        }
        $items = [];
        foreach ($query->order(['title' => 'ASC'])->limit(25) as $row) {
            $items[] = ['id' => $row->id, 'title' => $row->title];
        }
        return $this->json(['items' => $items]);
    }

    public function membership(string $id): Response
    {
        $this->request->allowMethod(['post']);
        $operation = $this->request->getData('operation');
        if (!in_array($operation, ['add', 'remove'], true)) {
            throw new BadRequestException('Choose add or remove association.');
        }
        $card = $this->getCardTable()->get($this->request->getData('cardId'));
        $groups = $this->getCardgroupTable();
        return $groups->getConnection()->transactional(function () use ($groups, $id, $card, $operation) {
            $group = $this->lockedRecord($groups, $id);
            if (!hash_equals($this->revision('groups', $group), (string)$this->request->getData('revision'))) {
                return $this->json(['message' => 'Group membership changed. Reload the group before continuing.'], 409);
            }
            $links = $this->getCardcardgroupTable();
            $condition = ['card_group_id' => $group->id, 'card_id' => $card->id];
            if ($operation === 'remove') {
                if ($links->find()->where(['card_group_id' => $group->id])->count() <= 1) {
                    return $this->json(['message' => 'A group must contain at least one card.'], 422);
                }
                $links->deleteAll($condition);
            } elseif (!$links->exists($condition)) {
                $links->saveOrFail($links->newEntity($condition));
            }
            Log::info('Learning workspace membership: ' . json_encode([
                'group' => $group->id, 'card' => $card->id, 'operation' => $operation,
                'user' => $this->getAuthUser()->id,
            ]));
            return $this->json($this->detail('groups', $id));
        });
    }

    public function archive(): Response
    {
        $this->request->allowMethod(['get']);
        $files = (new ImageArchive())->files();
        $search = mb_strtolower(trim((string)$this->request->getQuery('q', '')));
        $files = array_values(array_filter($files, fn($file) => $search === ''
            || str_contains(mb_strtolower($file['name']), $search)));
        $page = max(1, (int)$this->request->getQuery('page', 1));
        return $this->json([
            'items' => array_slice($files, ($page - 1) * 24, 24),
            'page' => $page, 'total' => count($files), 'pageSize' => 24,
        ]);
    }

    public function media(): Response
    {
        $this->request->allowMethod(['get']);
        $kind = (string)$this->request->getQuery('kind');
        if (!in_array($kind, ['image', 'audio', 'video'], true)) {
            throw new BadRequestException('Unknown media type.');
        }
        $q = mb_substr(trim((string)$this->request->getQuery('q', '')), 0, 200);
        $query = $this->getFilesTable()->find()->where(['type' => $kind]);
        if ($q !== '') {
            $query->where(['OR' => ['name LIKE' => '%' . $q . '%', 'file_name LIKE' => '%' . $q . '%']]);
        }
        $total = $query->count();
        $page = max(1, (int)$this->request->getQuery('page', 1));
        $items = [];
        foreach ($query->order(['id' => 'DESC'])->limit(24)->offset(($page - 1) * 24) as $file) {
            $items[] = ['id' => $file->id, 'name' => $file->name, 'url' => $file->FullUrl, 'type' => $file->type];
        }
        return $this->json(['items' => $items, 'page' => $page, 'pageSize' => 24, 'total' => $total]);
    }

    public function importImage(): Response
    {
        $this->request->allowMethod(['post']);
        $key = $this->request->getData('key');
        foreach ((new ImageArchive())->files() as $image) {
            if ($image['key'] !== $key) {
                continue;
            }
            $table = $this->getFilesTable();
            $file = $table->find()->where(['aws_link' => $image['url']])->first();
            if (!$file) {
                $file = $table->newEntity([
                    'name' => $image['name'], 'description' => 'Shared image archive',
                    'file' => $image['name'], 'file_name' => $image['name'],
                    'aws_link' => $image['url'], 'type' => 'image',
                    'format' => strtolower(pathinfo($image['name'], PATHINFO_EXTENSION)),
                    'upload_user_id' => $this->getAuthUser()->id,
                ]);
                $table->saveOrFail($file);
            }
            return $this->json(['id' => $file->id, 'url' => $file->FullUrl, 'name' => $file->name]);
        }

        throw new NotFoundException('This image is not in the shared archive.');
    }

    public function upload(): Response
    {
        $this->request->allowMethod(['post']);
        if (!Configure::read('SITEUPLOAD') && !Configure::read('AWSUPLOAD')) {
            throw new BadRequestException('Media uploads are disabled by the site configuration.');
        }
        $file = $this->request->getUploadedFile('file');
        if (!$file || $file->getError() !== UPLOAD_ERR_OK || !$file->getSize() || $file->getSize() > 50 * 1024 * 1024) {
            throw new BadRequestException('Choose a recording or media file under 50 MB (and the server upload limit).');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getStream()->getMetadata('uri'));
        $formats = [
            'audio/mpeg' => ['audio', 'mp3'], 'audio/ogg' => ['audio', 'ogg'],
            'audio/wav' => ['audio', 'wav'], 'audio/x-wav' => ['audio', 'wav'],
            'audio/webm' => ['audio', 'webm'], 'video/webm' => ['video', 'webm'],
            'video/mp4' => ['video', 'mp4'], 'audio/mp4' => ['audio', 'm4a'],
            'image/png' => ['image', 'png'], 'image/jpeg' => ['image', 'jpg'],
            'image/gif' => ['image', 'gif'], 'image/webp' => ['image', 'webp'],
        ];
        if (!isset($formats[$mime])) {
            throw new BadRequestException('Unsupported media format.');
        }
        [$type, $ext] = $formats[$mime];
        // WebM containers are often identified as video even for audio-only capture.
        if ($mime === 'video/webm' && $this->request->getData('kind') === 'audio') {
            $type = 'audio';
        }
        $filename = 'workspace-' . bin2hex(random_bytes(16)) . '.' . $ext;
        $dir = WWW_ROOT . 'img/UploadedFile/';
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new \RuntimeException('Unable to create the media upload directory.');
        }
        $file->moveTo($dir . $filename);
        $url = '';
        try {
            if (Configure::read('AWSUPLOAD')) {
                $this->loadComponent('FilesCommon');
                $result = $this->FilesCommon->uploadFileToAws($dir . $filename, $filename, 'FILES');
                if (($result['status'] ?? 0) != 1) {
                    throw new \RuntimeException('The media storage service rejected the upload.');
                }
                $url = (string)$result['result']['ObjectURL'];
            }
            $entity = $this->getFilesTable()->newEntity([
                'name' => mb_substr((string)$this->request->getData('name', $filename), 0, 200),
                'description' => 'Learning workspace media', 'file' => $filename, 'file_name' => $filename,
                'type' => $type, 'format' => $ext, 'aws_link' => $url,
                'upload_user_id' => $this->getAuthUser()->id,
            ]);
            $this->getFilesTable()->saveOrFail($entity);
        } catch (\Throwable $error) {
            // Do not leave an unregistered local upload after a storage or validation failure.
            if (!unlink($dir . $filename)) {
                Log::error('Unable to clean failed workspace upload: ' . $filename);
            }
            throw $error;
        }

        return $this->json(['id' => $entity->id, 'url' => $entity->FullUrl, 'name' => $entity->name, 'type' => $type]);
    }

    private function detail(string $type, string $id): array
    {
        $definition = LearningWorkspace::definition($type);
        $table = $this->getTableLocator()->get($definition['table']);
        $entity = $table->get($id);
        $fields = [];
        foreach ($definition['fields'] as $field) {
            $fields[$field] = $entity->get($field);
        }
        $media = [];
        foreach ($definition['media'] as $field => $kind) {
            $fields[$field] = $entity->get($field);
            $file = $entity->get($field) ? $this->getFilesTable()->find()
                ->where(['id' => $entity->get($field)])->first() : null;
            $media[] = [
                'field' => $field, 'kind' => $kind, 'id' => $entity->get($field),
                'url' => $file ? $file->FullUrl : null, 'missing' => $entity->get($field) && !$file,
            ];
        }
        $relations = [];
        foreach (LearningWorkspace::RELATIONS[$type] as [$target]) {
            $query = $this->getTableLocator()->get(LearningWorkspace::definition($target)['table'])->find();
            $this->restrictRelation($query, $type, (int)$id, $target);
            $relations[] = ['type' => $target, 'label' => LearningWorkspace::TYPES[$target]['label'],
                'count' => $query->count()];
        }
        $summary = (new WorkspacePresentation($this->getTableLocator()))
            ->decorate([$this->summary($type, $entity)])[0];
        return $summary + [
            'fields' => $fields, 'revision' => $this->revision($type, $entity),
            'media' => $media, 'relations' => $relations,
            'created' => $entity->get('created'), 'modified' => $entity->get('modified'),
        ];
    }

    private function summary(string $type, $entity): array
    {
        $definition = LearningWorkspace::definition($type);
        $editor = null;
        if (isset($definition['editor'])) {
            [$controller, $action] = $definition['editor'];
            $editor = Router::url(['prefix' => 'Admin', 'controller' => $controller,
                'action' => $action, $entity->id]);
        }
        return [
            'id' => $entity->id, 'type' => $type, 'title' => $entity->get($definition['title']),
            'subtitle' => $type === 'cards' ? $entity->get('english') : null, 'editor' => $editor,
        ];
    }

    private function restrictRelation($query, string $source, int $id, string $target): void
    {
        LearningWorkspace::definition($source);
        if ($id < 1) {
            throw new BadRequestException('A valid parent ID is required.');
        }
        if ($source === 'lessons' && $target === 'cards') {
            $frames = $this->getLessonFramesTable()->find()->select(['id'])->where(['lesson_id' => $id]);
            $cards = $this->getLessonFrameBlocksTable()->find()->select(['card_id'])
                ->where(['lesson_frame_id IN' => $frames, 'type' => 'card', 'card_id IS NOT' => null]);
            $query->where(['Card.id IN' => $cards]);
            return;
        }
        if ($source === 'cards' && $target === 'lessons') {
            $blocks = $this->getLessonFrameBlocksTable()->find()->select(['lesson_frame_id'])
                ->where(['card_id' => $id, 'type' => 'card']);
            $lessons = $this->getLessonFramesTable()->find()->select(['lesson_id'])->where(['id IN' => $blocks]);
            $query->where(['Lessons.id IN' => $lessons]);
            return;
        }

        foreach (LearningWorkspace::RELATIONS[$source] as [$related, $junction, $sourceKey, $targetKey]) {
            if ($related !== $target) {
                continue;
            }
            $table = $this->getTableLocator()->get($junction);
            $subquery = $table->find()->select([$targetKey])->where([$sourceKey => $id, $targetKey . ' IS NOT' => null]);
            if ($source === 'levels' && $target === 'units' && $this->request->getQuery('pathId')) {
                $subquery->where(['learningpath_id' => (int)$this->request->getQuery('pathId')]);
            }
            $query->where([$query->getRepository()->aliasField('id') . ' IN' => $subquery]);
            return;
        }
        throw new BadRequestException('These record types do not have a supported relationship.');
    }

    private function lockedRecord($table, string $id)
    {
        $query = $table->find()->where([$table->aliasField('id') => $id]);
        if (!($table->getConnection()->getDriver() instanceof \Cake\Database\Driver\Sqlite)) {
            $query->epilog('FOR UPDATE');
        }
        return $query->firstOrFail();
    }

    private function revision(string $type, $entity): string
    {
        $revision = LearningWorkspace::revision($entity);
        if ($type === 'groups') {
            $ids = $this->getCardcardgroupTable()->find()->select(['id', 'card_id'])
                ->where(['card_group_id' => $entity->id])->order(['id' => 'ASC'])->disableHydration()->toArray();
            $revision = hash('sha256', $revision . json_encode($ids, JSON_THROW_ON_ERROR));
        }
        return $revision;
    }

    private function json(array $data, int $status = 200): Response
    {
        return $this->response->withStatus($status)->withType('json')->withHeader('Cache-Control', 'no-store')
            ->withStringBody(json_encode($data, JSON_THROW_ON_ERROR));
    }
}
