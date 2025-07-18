<?php

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class LearningpathsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('learningpaths');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->hasMany('Users', [
            'foreignKey' => 'learningpath_id'
        ]);

        $this->hasMany('Pathlevel', [
            'foreignKey' => 'learningpath_id'
        ]);

        $this->belongsToMany('Levels', [
            'through' => 'Pathlevel'
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

    /**
     * Default validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('label', "Path name can't be blank")
            ->notEmptyString('description', "Path description can't be blank");

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(array('label')));
        return $rules;
    }
}
