<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class FilesTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('files');
        $this->setPrimaryKey('id');

        $this->belongsTo('User', [
            'foreignKey' => 'upload_user_id',
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

    /**
     * Default validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('name', 'Please Enter Name')
            ->requirePresence('description', 'Please Enter Description')
            ->requirePresence(['file' => ['mode' => 'create', 'message' => 'Please select a file.']])
            ->requirePresence(['file_name' => ['mode' => 'create', 'message' => 'Please select a valid file.']]);

        return $validator;
    }
}
