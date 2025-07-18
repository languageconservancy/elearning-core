<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class DictionaryTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('reference_dictionary');
        $this->setPrimaryKey('id');

        $this->hasOne('Inflections', [
            'foreignKey' => 'reference_dictionary_id'
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('lakota', "Lakota can't be blank")
            ->notEmptyString('English', "English can't be blank")
            ->notEmptyString('part_of_speech', "Part of speech can't be blank");
        return $validator;
    }
}
