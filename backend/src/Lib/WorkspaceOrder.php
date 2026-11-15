<?php

namespace App\Lib;

use Cake\Database\Driver\Sqlite;
use Cake\Http\Exception\ConflictException;
use Cake\ORM\Locator\LocatorInterface;
use Cake\ORM\Query;
use InvalidArgumentException;

/**
 * Drag-and-drop ordering for the sequenced links between learning records.
 */
class WorkspaceOrder
{
    public const PAGE_SIZE = 200;

    /** scope => [table, parent key, order column, child key, parent type, child type] */
    public const SCOPES = [
        'path-levels' => ['Pathlevel', 'learningpath_id', 'sequence', 'level_id', 'paths', 'levels'],
        'level-units' => ['LevelUnits', 'level_id', 'sequence', 'unit_id', 'levels', 'units'],
        'unit-activities' => ['Unitdetails', 'unit_id', 'sequence', null, 'units', 'activities'],
        'lesson-frames' => ['LessonFrames', 'lesson_id', 'frameorder', null, 'lessons', 'frames'],
    ];

    private LocatorInterface $tables;

    public function __construct(LocatorInterface $tables)
    {
        $this->tables = $tables;
    }

    public static function scopeFor(string $parentType, string $childType): ?string
    {
        foreach (self::SCOPES as $scope => $config) {
            if ($config[4] === $parentType && $config[5] === $childType) {
                return $scope;
            }
        }
        return null;
    }

    /**
     * Joins the sequenced link table so records list in their stored order and expose the link id.
     */
    public function orderRecords(Query $query, string $scope, int $parentId, ?int $pathId): void
    {
        [$tableName, $parentKey, $column, $childKey] = self::SCOPES[$scope];
        $link = $this->tables->get($tableName);
        $alias = $query->getRepository()->getAlias();
        $conditions = ["Link.$childKey = $alias.id", "Link.$parentKey" => $parentId];
        if ($scope === 'level-units') {
            $conditions['Link.learningpath_id'] = $pathId;
        }
        $query->select($query->getRepository())
            ->select(['link_id' => 'Link.id'])
            ->innerJoin(['Link' => $link->getTable()], $conditions)
            ->order(["Link.$column" => 'ASC', 'Link.id' => 'ASC']);
    }

    /**
     * Rewrites the order of every link under a parent. The submitted ids must be exactly the
     * current set, so a list that changed elsewhere is rejected rather than partially reordered.
     */
    public function reorder(string $scope, int $parentId, ?int $pathId, $ids): array
    {
        if (!isset(self::SCOPES[$scope])) {
            throw new InvalidArgumentException('This list cannot be reordered.');
        }
        if ($parentId < 1 || ($scope === 'level-units' && (!$pathId || $pathId < 1))) {
            throw new InvalidArgumentException('A valid parent is required to reorder this list.');
        }
        if (!is_array($ids) || !array_is_list($ids) || !$ids || count($ids) > self::PAGE_SIZE) {
            throw new InvalidArgumentException('Submit the complete list order.');
        }
        $ids = array_map(function ($id) {
            $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) {
                throw new InvalidArgumentException('List item IDs must be positive integers.');
            }
            return $id;
        }, $ids);
        if (count(array_unique($ids)) !== count($ids)) {
            throw new InvalidArgumentException('A list item was submitted twice.');
        }
        [$tableName, $parentKey, $column] = self::SCOPES[$scope];
        $table = $this->tables->get($tableName);

        return $table->getConnection()->transactional(function () use ($table, $scope, $parentKey, $column, $parentId, $pathId, $ids) {
            $conditions = [$parentKey => $parentId];
            if ($scope === 'level-units') {
                $conditions['learningpath_id'] = $pathId;
            }
            $query = $table->find()->where($conditions);
            if (!($table->getConnection()->getDriver() instanceof Sqlite)) {
                $query->epilog('FOR UPDATE');
            }
            $rows = [];
            foreach ($query as $row) {
                $rows[(int)$row->id] = $row;
            }
            $current = array_keys($rows);
            $submitted = $ids;
            sort($current);
            sort($submitted);
            if ($current !== $submitted) {
                throw new ConflictException('This list changed elsewhere. Reload it before reordering.');
            }
            $changed = [];
            foreach ($ids as $index => $id) {
                if ((int)$rows[$id]->get($column) !== $index + 1) {
                    $rows[$id]->set($column, $index + 1);
                    $changed[] = $rows[$id];
                }
            }
            if ($changed) {
                $table->saveManyOrFail($changed);
            }
            return $ids;
        });
    }
}
