<?php

namespace App\Lib;

use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Locator\LocatorInterface;

class WorkspacePresentation
{
    private LocatorInterface $tables;

    public function __construct(LocatorInterface $tables)
    {
        $this->tables = $tables;
    }

    public function decorate(array $items): array
    {
        $byType = [];
        foreach ($items as $item) {
            $byType[$item['type']][] = $item['id'];
        }
        $metadata = [];
        $cardIds = $byType['cards'] ?? [];
        if (!empty($byType['lessons'])) {
            $frames = $this->tables->get('LessonFrames');
            $query = $frames->find()->where(['lesson_id IN' => $byType['lessons']]);
            $query->select(['lesson_id', 'frame_count' => $query->func()->count('*'),
                'duration' => $query->func()->sum('duration'), 'audio_id' => $query->func()->min('audio_id')])
                ->group(['lesson_id']);
            foreach ($query as $row) {
                $metadata['lessons'][$row->lesson_id] = [
                    'labels' => [(int)$row->frame_count . ' frames', round((float)$row->duration, 1) . ' s'],
                    'audioId' => $row->audio_id,
                ];
            }
            $blocks = $this->tables->get('LessonFrameBlocks')->find()
                ->innerJoin(['Frame' => 'lesson_frames'], ['Frame.id = LessonFrameBlocks.lesson_frame_id'])
                ->where(['Frame.lesson_id IN' => $byType['lessons']]);
            $blocks->select(['lesson_id' => 'Frame.lesson_id', 'block_count' => $blocks->func()->count('*'),
                'card_count' => new QueryExpression('COUNT(DISTINCT CASE WHEN LessonFrameBlocks.type = \'card\' THEN card_id END)'),
                'preview_card' => new QueryExpression('MIN(CASE WHEN LessonFrameBlocks.type = \'card\' THEN card_id END)')])
                ->group(['Frame.lesson_id']);
            foreach ($blocks as $row) {
                $meta = &$metadata['lessons'][$row->lesson_id];
                $meta['labels'][] = (int)$row->block_count . ' blocks';
                $meta['labels'][] = (int)$row->card_count . ' cards';
                $meta['cardIds'] = array_filter([$row->preview_card]);
                $cardIds = array_merge($cardIds, $meta['cardIds']);
                unset($meta);
            }
        }
        if (!empty($byType['exercises'])) {
            foreach ($this->tables->get('Exercises')->find()->where(['id IN' => $byType['exercises']]) as $exercise) {
                $pair = array_map(fn($key) => ExerciseContent::MODALITIES[$key] ?? $key, explode('-', $exercise->promteresponsetype ?? ''));
                $labels = [
                    ExerciseContent::TYPES[$exercise->exercise_type] ?? 'Not configured',
                    ['card' => 'Cards', 'card_group' => 'Card groups', 'custom' => 'Custom content'][$exercise->card_type] ?? '',
                    implode(' → ', $pair), 'Bonus ' . ($exercise->bonus ?? 0),
                ];
                if ($exercise->exercise_type === VocabBuilder::TYPE) {
                    $labels[] = VocabBuilder::ACTIVITIES[$exercise->vbl_activity]['label'] ?? 'Select vocabulary activity';
                    $labels[] = ($exercise->vbl_mode ?? 'word') . ' mode';
                }
                $metadata['exercises'][$exercise->id] = [
                    'labels' => array_values(array_filter($labels)),
                    'description' => strip_tags((string)$exercise->instruction),
                ];
            }
            $options = $this->tables->get('Exerciseoptions')->find()->where(['exercise_id IN' => $byType['exercises']]);
            $options->select(['exercise_id', 'count' => $options->func()->count('*'),
                'prompt' => $options->func()->min('card_id'), 'response' => $options->func()->min('responce_card_id')])
                ->group(['exercise_id']);
            foreach ($options as $row) {
                $metadata['exercises'][$row->exercise_id]['labels'][] = (int)$row->count . ' content items';
                $ids = array_values(array_unique(array_filter([$row->prompt, $row->response])));
                $metadata['exercises'][$row->exercise_id]['cardIds'] = $ids;
                $cardIds = array_merge($cardIds, $ids);
            }
        }
        foreach (['lessons' => 'lesson_id', 'exercises' => 'exercise_id'] as $type => $key) {
            if (empty($byType[$type])) {
                continue;
            }
            $query = $this->tables->get('Unitdetails')->find()->where([$key . ' IN' => $byType[$type]]);
            $query->select([$key, 'uses' => new QueryExpression('COUNT(DISTINCT unit_id)')])->group([$key]);
            foreach ($query as $row) {
                $metadata[$type][$row->$key]['labels'][] = (int)$row->uses . ' unit uses';
            }
        }
        $previews = $this->cards(array_values(array_unique($cardIds)));
        $audioIds = [];
        foreach ($metadata['lessons'] ?? [] as $meta) {
            if (!empty($meta['audioId'])) {
                $audioIds[] = $meta['audioId'];
            }
        }
        $audio = $this->files($audioIds);
        return array_map(function ($item) use ($metadata, $previews, $audio) {
            $meta = $metadata[$item['type']][$item['id']] ?? ['labels' => []];
            $item['metadata'] = $meta['labels'];
            $item['description'] = $meta['description'] ?? '';
            $ids = array_map('intval', $item['type'] === 'cards' ? [$item['id']] : ($meta['cardIds'] ?? []));
            $item['previewCards'] = array_values(array_intersect_key($previews, array_flip($ids)));
            $item['audioUrl'] = isset($meta['audioId']) ? ($audio[$meta['audioId']] ?? null) : null;
            return $item;
        }, $items);
    }

    public function cards(array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $cards = $this->tables->get('Card')->find()->where(['id IN' => $ids])->toArray();
        $fileIds = [];
        foreach ($cards as $card) {
            $fileIds[] = $card->image_id;
            $fileIds = array_merge($fileIds, array_map('trim', explode(',', (string)$card->audio)));
        }
        $files = $this->files($fileIds);
        $result = [];
        foreach ($cards as $card) {
            $result[$card->id] = [
                'id' => $card->id,
                'title' => html_entity_decode(strip_tags((string)$card->lakota), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'english' => html_entity_decode(strip_tags((string)$card->english), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'imageUrl' => $files[$card->image_id] ?? null,
                'audioUrls' => array_values(array_intersect_key($files, array_flip(array_map('trim', explode(',', (string)$card->audio))))),
            ];
        }
        return $result;
    }

    public function files(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids, fn($id) => is_numeric($id) && (int)$id > 0)));
        if (!$ids) {
            return [];
        }
        $result = [];
        foreach ($this->tables->get('Files')->find()->where(['id IN' => $ids]) as $file) {
            $result[$file->id] = $file->FullUrl;
        }
        return $result;
    }
}
