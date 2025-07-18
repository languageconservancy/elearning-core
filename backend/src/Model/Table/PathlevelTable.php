<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class PathlevelTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('path_levels');
        $this->setPrimaryKey('id');
        $this->belongsTo('Learningpaths', [
            'foreignKey' => 'learningpath_id'
        ]);
        $this->belongsTo('Levels', [
            'foreignKey' => 'level_id'
        ]);

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created' => 'new',
                    'modified' => 'always',
                ]
            ]
        ]);
    }

    public function resequence(int $pathId): bool
    {
        // Ensure args are valid
        if (empty($pathId)) {
            Log::error("Pathlevel resequence not possible with undefined pathId");
            return false;
        }
        // Get all level units from this level
        $pathLevels = $this->find()
            ->where(['learningpath_id' => $pathId])
            ->order(['sequence' => 'ASC'])
            ->all();

        // Check if we didn't find any
        if (empty($pathLevels)) {
            Log::error("No path levels found with path ID " . $pathId);
            return false;
        }

        // Resequence path levels
        foreach ($pathLevels as $idx => $pathLevel) {
            $pathLevel->sequence = ($idx + 1);
        }

        // Save newly sequenced level units to database
        if (!$this->saveMany($pathLevels)) {
            Log::error("Error resequencing path levels");
            return false;
        }

        return true;
    }
}
