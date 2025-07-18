<?php

namespace App\Model\Table;

use App\Model\Entity\Grade;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Grades Model
 *
 * @method Grade get($primaryKey, $options = [])
 * @method Grade newEmptyEntity($data = null, array $options = [])
 * @method Grade[] newEntities(array $data, array $options = [])
 * @method Grade|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method Grade patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method Grade[] patchEntities($entities, array $data, array $options = [])
 * @method Grade findOrCreate($search, callable $callback = null, $options = [])
 */
class GradesTable extends Table
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

        $this->setTable('grades');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', 'create');

        $validator
            ->scalar('grade')
            ->maxLength('grade', 50)
            ->requirePresence('grade', 'create')
            ->notEmptyString('grade');

        return $validator;
    }
}
