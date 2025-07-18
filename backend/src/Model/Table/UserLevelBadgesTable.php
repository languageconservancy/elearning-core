<?php

namespace App\Model\Table;

use App\Model\Entity\UserLevelBadge;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UserLevelBadges Model
 *
 * @property UsersTable|BelongsTo $Users
 * @property LevelsTable|BelongsTo $Levels
 * @property LearningpathsTable|BelongsTo $Learningpaths
 *
 * @method UserLevelBadge get($primaryKey, $options = [])
 * @method UserLevelBadge newEmptyEntity($data = null, array $options = [])
 * @method UserLevelBadge[] newEntities(array $data, array $options = [])
 * @method UserLevelBadge|bool save(EntityInterface $entity, $options = [])
 * @method UserLevelBadge patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method UserLevelBadge[] patchEntities($entities, array $data, array $options = [])
 * @method UserLevelBadge findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin TimestampBehavior
 */
class UserLevelBadgesTable extends Table
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

        $this->setTable('user_level_badges');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Levels', [
            'foreignKey' => 'level_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Learningpaths', [
            'foreignKey' => 'path_id',
            'joinType' => 'INNER'
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
        $rules->add($rules->existsIn(['level_id'], 'Levels'));
        $rules->add($rules->existsIn(['path_id'], 'Learningpaths'));

        return $rules;
    }
}
