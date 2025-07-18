<?php

namespace App\Model\Table;

use App\Model\Entity\Emailcontent;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Emailcontents Model
 *
 * @method Emailcontent get($primaryKey, $options = [])
 * @method Emailcontent newEmptyEntity($data = null, array $options = [])
 * @method Emailcontent[] newEntities(array $data, array $options = [])
 * @method Emailcontent|bool save(EntityInterface $entity, $options = [])
 * @method Emailcontent patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method Emailcontent[] patchEntities($entities, array $data, array $options = [])
 * @method Emailcontent findOrCreate($search, callable $callback = null, $options = [])
 */
class EmailcontentsTable extends Table
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

        $this->setTable('emailcontents');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
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
            ->integer('id')
            ->allowEmptyString('id', 'create');

        $validator
            ->allowEmptyString('display_name');

        $validator
            ->allowEmptyString('key');

        $validator
            ->allowEmptyString('subject');

        $validator
            ->allowEmptyString('content');

        return $validator;
    }
}
