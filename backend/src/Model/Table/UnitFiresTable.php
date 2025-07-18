<?php

namespace App\Model\Table;

use App\Model\Entity\UnitFire;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UnitFires Model
 *
 * @property UsersTable|BelongsTo $Users
 * @property UnitsTable|BelongsTo $Units
 *
 * @method UnitFire get($primaryKey, $options = [])
 * @method UnitFire newEmptyEntity($data = null, array $options = [])
 * @method UnitFire[] newEntities(array $data, array $options = [])
 * @method UnitFire|bool save(EntityInterface $entity, $options = [])
 * @method UnitFire patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method UnitFire[] patchEntities($entities, array $data, array $options = [])
 * @method UnitFire findOrCreate($search, callable $callback = null, $options = [])
 */
class UnitFiresTable extends Table
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

        $this->setTable('unit_fires');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);
        $this->belongsTo('Units', [
            'foreignKey' => 'unit_id'
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
            ->integer('id')
            ->allowEmptyString('id', 'create');

        $validator
            ->integer('reading_persantage')
            ->allowEmptyString('reading_persantage');

        $validator
            ->integer('writing_percentage')
            ->allowEmptyString('writing_percentage');

        $validator
            ->integer('listening_percentage')
            ->allowEmptyString('listening_percentage');

        $validator
            ->integer('speaking_percentage')
            ->allowEmptyString('speaking_percentage');

        $validator
            ->integer('total_persentage')
            ->allowEmptyString('total_persentage');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param RulesChecker $rules The rules object to be modified.
     * @return RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['unit_id'], 'Units'));

        return $rules;
    }
}
