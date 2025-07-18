<?php

namespace App\Model\Table;

use App\Model\Entity\ReviewVar;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ReviewVars Model
 *
 * @method ReviewVar get($primaryKey, $options = [])
 * @method ReviewVar newEmptyEntity($data = null, array $options = [])
 * @method ReviewVar[] newEntities(array $data, array $options = [])
 * @method ReviewVar|bool save(EntityInterface $entity, $options = [])
 * @method ReviewVar patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method ReviewVar[] patchEntities($entities, array $data, array $options = [])
 * @method ReviewVar findOrCreate($search, callable $callback = null, $options = [])
 */
class ReviewVarsTable extends Table
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

        $this->setTable('review_vars');
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
            ->scalar('key')
            ->maxLength('key', 50)
            ->allowEmptyString('key');

        $validator
            ->integer('value')
            ->requirePresence('value', 'create')
            ->notEmptyString('value');

        return $validator;
    }
}
