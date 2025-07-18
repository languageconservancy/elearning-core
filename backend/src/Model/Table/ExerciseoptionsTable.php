<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class ExerciseoptionsTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('exercise_options');
        $this->setPrimaryKey('id');

        $this->belongsTo('Exercises', [
            'foreignKey' => 'exercise_id'
        ]);

        $this->hasMany('ExerciseCustomOptions', [
            'foreignKey' => 'exercise_option_id'
        ]);
    }
}
