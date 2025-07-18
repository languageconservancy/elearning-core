<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class RecordingAudiosTable extends Table
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
        $this->setTable('recording_audios');
        $this->setPrimaryKey('id');
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);
        $this->belongsTo('Exercises', [
            'foreignKey' => 'exercise_id'
        ]);
        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created' => 'new'
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
            ->integer('id')
            ->allowEmptyString('id', 'create');
//
//        $validator
//            ->integer('file_name')
//            ->allowEmptyString('file_name');

//        $validator
//            ->scalar('aws_link')
//            ->maxLength('aws_link', 255)
//            ->allowEmptyString('aws_link');
//
//        $validator
//            ->scalar('type')
//            ->allowEmptyString('type');

        return $validator;
    }
}
