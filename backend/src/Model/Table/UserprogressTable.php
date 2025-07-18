<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class UserprogressTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('user_progress');
        $this->setPrimaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);

        $this->belongsTo('Learningpaths', [
            'foreignKey' => 'learningpath_id'
        ]);

        $this->belongsTo('Levels', [
            'foreignKey' => 'level_id'
        ]);

        $this->belongsTo('Units', [
            'foreignKey' => 'unit_id'
        ]);

        $this->belongsTo('Lessons', [
            'foreignKey' => 'lesson_id'
        ]);

        $this->belongsTo('Exercises', [
            'foreignKey' => 'exercise_id'
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
