<?php

namespace App\Model\Table;

use App\Model\Entity\PointReference;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PointReferences Model
 *
 * @method PointReference get($primaryKey, $options = [])
 * @method PointReference newEmptyEntity($data = null, array $options = [])
 * @method PointReference[] newEntities(array $data, array $options = [])
 * @method PointReference|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method PointReference patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method PointReference[] patchEntities($entities, array $data, array $options = [])
 * @method PointReference findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin TimestampBehavior
 */
class PointReferencesTable extends Table
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

        $this->setTable('point_references');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
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
        return $validator;
    }
}
