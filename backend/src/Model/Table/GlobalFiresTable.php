<?php

namespace App\Model\Table;

use App\Model\Entity\GlobalFire;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * GlobalFires Model
 *
 * @property UsersTable|BelongsTo $Users
 *
 * @method GlobalFire get($primaryKey, $options = [])
 * @method GlobalFire newEmptyEntity($data = null, array $options = [])
 * @method GlobalFire[] newEntities(array $data, array $options = [])
 * @method GlobalFire|bool save(EntityInterface $entity, $options = [])
 * @method GlobalFire patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method GlobalFire[] patchEntities($entities, array $data, array $options = [])
 * @method GlobalFire findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin TimestampBehavior
 */
class GlobalFiresTable extends Table
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

        $this->setTable('global_fires');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
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
            ->integer('fire_days')
            ->allowEmptyString('fire_days');

        $validator
            ->integer('streak_days')
            ->allowEmptyString('streak_days');

        $validator
            ->date('last_day')
            ->allowEmptyDate('last_day');

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

        return $rules;
    }
}
