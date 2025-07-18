<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class CardgrouptypeTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('card_group_types');
        $this->setPrimaryKey('id');
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
