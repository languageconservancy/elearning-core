<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class UnitsTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('units');
        $this->setPrimaryKey('id');

        $this->hasMany('LevelUnits', [
            'foreignKey' => 'unit_id'
        ]);

        $this->belongsToMany('Levels', [
            'through' => 'LevelUnits'
        ]);

        $this->belongsToMany('Lessons', [
            'through' => 'Unitdetails'
        ]);

        $this->belongsToMany('Exercises', [
            'through' => 'Unitdetails'
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->notEmptyString('name', "Unit name can't be blank")
            ->notEmptyString('description', "Unit description can't be blank");
        return $validator;
    }
}
