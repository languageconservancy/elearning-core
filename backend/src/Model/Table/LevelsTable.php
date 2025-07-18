<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class LevelsTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('levels');
        $this->setPrimaryKey('id');

        $this->hasMany('Pathlevel', [
            'foreignKey' => 'level_id'
        ]);

        $this->hasMany('LevelUnits', [
            'foreignKey' => 'level_id'
        ]);

        $this->hasMany('Classrooms', [
            'foreignKey' => 'level_id'
        ]);

        $this->belongsToMany('Learningpaths', [
            'through' => 'Pathlevel'
        ]);

        $this->belongsToMany('Units', [
            'through' => 'LevelUnits'
        ]);

        $this->belongsTo('image', [
            'className' => 'Files',
            'foreignKey' => 'image_id',
            'propertyName' => 'image',
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

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('name', "Level name can't be blank");
        return $validator;
    }
}
