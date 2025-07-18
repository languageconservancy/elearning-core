<?php

namespace App\Model\Table;

use App\Model\Entity\UserActivity;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UserActivities Model
 *
 * @property UsersTable|BelongsTo $Users
 *
 * @method UserActivity get($primaryKey, $options = [])
 * @method UserActivity newEmptyEntity($data = null, array $options = [])
 * @method UserActivity[] newEntities(array $data, array $options = [])
 * @method UserActivity|bool save(EntityInterface $entity, $options = [])
 * @method UserActivity patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method UserActivity[] patchEntities($entities, array $data, array $options = [])
 * @method UserActivity findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin TimestampBehavior
 */
class UserActivitiesTable extends Table
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

        $this->setTable('user_activities');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('cards', [
            'foreignKey' => 'card_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Exercises', [
            'foreignKey' => 'exercise_id',
            'joinType' => 'INNER'
        ]);
         // $this->belongsTo('Activities', [
         //     'foreignKey' => 'activity_id',
         //     'joinType' => 'INNER'
         // ]);
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
