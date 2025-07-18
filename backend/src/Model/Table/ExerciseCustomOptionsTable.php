<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ExerciseCustomOptionsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('exercise_custom_options');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created' => 'new',
                    'modified' => 'always',
                ]
            ]
        ]);

        $this->belongsTo('Exercises', [
            'foreignKey' => 'exercise_id'
        ]);
        $this->belongsTo('ExerciseOptions', [
            'foreignKey' => 'exercise_option_id'
        ]);
        $this->belongsTo('Files', [
            'foreignKey' => 'prompt_audio_id'
        ]);
        $this->belongsTo('Files', [
            'foreignKey' => 'prompt_image_id'
        ]);
        $this->belongsTo('Files', [
            'foreignKey' => 'response_audio_id'
        ]);
        $this->belongsTo('Files', [
            'foreignKey' => 'response_image_id'
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
//          $validator
//              ->integer('id')
//              ->allowEmptyString('id', 'create');
//
//          $validator
//              ->scalar('prompt_html')
//              ->allowEmptyString('prompt_html');
//
//          $validator
//              ->scalar('response_html')
//              ->allowEmptyString('response_html');
//
//          $validator
//              ->scalar('type')
//              ->allowEmptyString('type');

        return $validator;
    }
}
