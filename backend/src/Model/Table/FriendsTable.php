<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class FriendsTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('friends');
        $this->setPrimaryKey('id');

        $this->belongsTo('Friend', [
            'foreignKey' => 'friend_id',
            'className' => 'Users'
        ]);
        $this->belongsTo('User', [
            'foreignKey' => 'user_id',
            'className' => 'Users'
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
