<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Log\Log;

class LevelUnitsTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('level_units');
        $this->setPrimaryKey('id');
        $this->belongsTo('Levels', [
            'foreignKey' => 'level_id'
        ]);
        $this->belongsTo('Units', [
            'foreignKey' => 'unit_id',
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

    /**
     * Resequence level units in order to ensure sequence numbers start at 1
     * and are sequential. This is useful when a level unit is deleted or reordered.
     * @param int $pathId The learning path ID
     * @param int $levelId The level ID
     * @return bool True on success, false on failure
     */
    public function resequence(int $pathId, int $levelId): bool
    {
        // Ensure args are valid
        if (empty($pathId)) {
            Log::error("LevelUnits resequence not possible with undefined pathId");
            return false;
        }
        if (empty($levelId)) {
            Log::error("LevelUnits resequence not possible with undefined levelId");
            return false;
        }
        // Get all level units from this level
        $levelUnits = $this->find()
            ->where([
                'learningpath_id' => $pathId,
                'level_id' => $levelId
            ])
            ->order(['sequence' => 'ASC'])
            ->all();

        // Check if we didn't find any
        if (empty($levelUnits)) {
            Log::error("No level units found with path ID "
                . $pathId . " and level ID " . $levelId);
            return false;
        }

        // Resequence level units
        foreach ($levelUnits as $idx => $levelUnit) {
            $levelUnit->sequence = ($idx + 1);
        }

        // Save newly sequenced level units to database
        if (!$this->saveMany($levelUnits)) {
            Log::error("Error resequencing level units");
            return false;
        }

        return true;
    }


}
