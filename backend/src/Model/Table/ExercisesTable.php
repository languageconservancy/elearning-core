<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ExercisesTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('exercises');
        $this->setPrimaryKey('id');

//          $this->hasMany('Exerciseoption', [
//              'foreignKey' => 'exercise_id',
//              'className' => 'Exerciseoption'
//          ]);

        $this->hasMany('Exerciseoptions', [
            'foreignKey' => 'exercise_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created' => 'new',
                    'modified' => 'always',
                ]
            ]
        ]);

        $this->belongsToMany('Units', [
            'through' => 'Unitdetails'
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
            ->integer('id')
            ->allowEmptyString('id', 'create');

        return $validator
            ->notEmptyString('name', 'Please enter Name')
            ->add('name', 'id', [
                'rule' => function ($value, $context) {
                    if (!isset($context['data']['id'])) {
                        $count = $this
                            ->find()
                            ->where(['Exercises.name' => $context['data']['name']])
                            ->count();
                        if ($count == 0) {
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        $count = $this
                            ->find()
                            ->where(['Exercises.name' => $context['data']['name']])
                            ->where(['Exercises.id !=' => $context['data']['id']])
                            ->count();
                        if ($count == 0) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                },
                'message' => 'Exercise name is already exists.'
        ]);
    }
}
