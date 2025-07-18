<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;

class InflectionsTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('inflections');
        $this->setPrimaryKey('id');
        $this->belongsTo('Dictionary', [
            'foreignKey' => 'reference_dictionary_id'
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
        $validator
            ->notEmptyString('headword', "Headword can't be blank")
            ->notEmptyString('reference_dictionary_id', 'Please Select Dictionary')
            ->notEmptyString('FSTR_INEXACT', "FSTR_INEXACT can't be blank")
            ->notEmptyString('FSTR_HTML', "FSTR_HTML can't be blank")
            ->notEmptyString('GSTR', "GSTR can't be blank")
            ->notEmptyString('PS', "PS can't be blank")
            ->add('reference_dictionary_id', 'reference_dictionary_id', [
                'rule' => function ($value, $context) {
                    $this->Dictionary = TableRegistry::getTableLocator()->get('Dictionary');
                    $count = $this->Dictionary->find()->where(['Dictionary.id' => $value])->count();
                    return $count > 0;
                },
                'message' => 'Please check the Dictionary Id'
            ]);
        return $validator;
    }

//      public function validateForeignKey($field) {
//          $this->Dictionary = TableRegistry::getTableLocator()->get('Dictionary');
//          $count = $this->Dictionary->find('count', array('conditions' => array('Dictionary.id' => $field)));
//          return $count > 0;
//      }
}
