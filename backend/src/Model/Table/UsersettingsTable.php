<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class UsersettingsTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('user_settings');
        $this->setPrimaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'lastupdated' => 'always',
                ]
            ]
        ]);
    }
}
