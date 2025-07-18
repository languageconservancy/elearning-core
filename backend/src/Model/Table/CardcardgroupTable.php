<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class CardcardgroupTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('card_card_groups');
        $this->setPrimaryKey('id');

        $this->belongsTo('Cardgroup', [
            'foreignKey' => 'card_group_id',
            'dependent' => true,
        ]);

        $this->belongsTo('Card', [
            'foreignKey' => 'card_id',
            'dependent' => true,
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
