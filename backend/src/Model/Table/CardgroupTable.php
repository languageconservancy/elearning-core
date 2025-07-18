<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class CardgroupTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('card_groups');
        $this->setPrimaryKey('id');

        $this->belongsTo('Cardgrouptype', [
            'foreignKey' => 'card_group_type_id'
        ]);

        $this->hasMany('Cardcardgroup', [
            'foreignKey' => 'card_group_id',
            'dependent' => true,
            'cascadeCallbacks' => true
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
        return $validator
            ->notEmptyString('name', 'Please enter Name')
            ->notEmptyString('card_group_type_id', 'Please Select Group Type');
    }
}
