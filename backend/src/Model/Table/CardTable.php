<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class CardTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('cards');
        $this->setPrimaryKey('id');
        $this->belongsTo('Dictionary', [
            'foreignKey' => 'reference_dictionary_id'
        ]);
        $this->belongsTo('Inflections', [
            'foreignKey' => 'inflection_id'
        ]);
        $this->hasMany('user_activities', [
            'foreignKey' => 'card_id',
            'dependent' => true,
            'cascadeCallbacks' => true
        ]);

        $this->belongsTo('image', [
            'className' => 'Files',
            'foreignKey' => 'image_id',
            'propertyName' => 'image',
        ]);

        $this->belongsTo('video', [
            'className' => 'Files',
            'foreignKey' => 'video_id',
            'propertyName' => 'video',
        ]);

        $this->belongsTo('audio_details', [
            'className' => 'Files',
            'foreignKey' => 'audio',
            'propertyName' => 'audio_details',
        ]);

        $this->belongsTo('Cardtype', [
            'foreignKey' => 'card_type_id'
        ]);

        $this->hasMany('Cardcardgroup', [
            'foreignKey' => 'card_id',
            'dependent' => true,
            'cascadeCallbacks' => true
        ]);

        $this->hasMany('LessonFrameBlocks', [
            'foreignKey' => 'card_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
            'conditions' => array('LessonFrameBlocks.type' => 'card')
        ]);

//          $this->hasMany('Exerciseoptions', [
//              ['foreignKey' => 'responce_card_id'],
//              ['foreignKey' => 'card_id'],
//              'dependent' => true,
//              'cascadeCallbacks' => true
//          ]);

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
            // ->notEmptyString('card_type_id', 'Please Enter Type')
            ->notEmptyString('lakota', 'Please Enter Lakota')
            ->notEmptyString('english', 'Please Enter English');
            // ->notEmptyString('gender', 'Please Enter Gender');
//              ->notEmpty('alt_lakota', 'Please Enter Alternate Lakota')
//              ->notEmpty('alt_english', 'Please Enter Alternate English')
//              ->notEmpty('reference_dictionary_id', 'Please Select Reference Dictionary');
//              ->notEmpty('audio', 'Please Enter Audio')
//              ->notEmpty('image_id', 'Please Enter Image')
//              ->notEmpty('video_id', 'Please Enter Video');

        return $validator;
    }

    public function getCardDetails($cardIds)
    {
        $cards = $this->find()
            ->contain(['Cardtype', 'image', 'video'])
            ->where(['Card.id IN' => $cardIds]);

        if (count($cardIds) == 1) {
            return $cards[0];
        } else {
            return $cards;
        }
    }

    public function findById($id)
    {
        $card = $this->find()
            ->contain(['Cardtype', 'image', 'video'])
            ->where(['Card.id' => $id])
            ->first();

        if ($card) {
            return $card;
        } else {
            return false;
        }
    }
}
