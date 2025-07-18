<?php

namespace App\Model\Table;

use App\Model\Entity\BonusPoint;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * BonusPoints Model
 *
 * @method BonusPoint get($primaryKey, $options = [])
 * @method BonusPoint newEmptyEntity($data = null, array $options = [])
 * @method BonusPoint[] newEntities(array $data, array $options = [])
 * @method BonusPoint|bool save(EntityInterface $entity, $options = [])
 * @method BonusPoint patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method BonusPoint[] patchEntities($entities, array $data, array $options = [])
 * @method BonusPoint findOrCreate($search, callable $callback = null, $options = [])
 */
class BonusPointsTable extends Table
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

        $this->setTable('bonus_points');
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
        //      $validator
        //          ->integer('id')
        //          ->allowEmptyString('id', 'create');
//
//          $validator
//              ->scalar('key')
//              ->maxLength('key', 255)
//              ->allowEmptyString('key');
//
//          $validator
//              ->integer('points')
//              ->allowEmptyString('points');

        return $validator;
    }
}
