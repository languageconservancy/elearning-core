<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class UnitdetailsTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('unit_details');
        $this->setPrimaryKey('id');
        $this->belongsTo('Lessons', [
            'foreignKey' => 'lesson_id'
        ]);
        $this->belongsTo('Exercises', [
            'foreignKey' => 'exercise_id'
        ]);
        $this->belongsTo('Units', [
            'foreignKey' => 'unit_id'
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
}
