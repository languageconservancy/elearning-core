<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class LearningspeedTable extends Table
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
        $this->setTable('learningspeed');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
        $this->hasMany('Users', [
            'foreignKey' => 'learningspeed_id'
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
