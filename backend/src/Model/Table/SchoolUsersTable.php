<?php

namespace App\Model\Table;

use App\Model\Entity\SchoolUser;
use Cake\Datasource\EntityInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SchoolUsers Model
 *
 * @property SchoolsTable|\Cake\ORM\Association\BelongsTo $Schools
 * @property UsersTable|\Cake\ORM\Association\BelongsTo $Users
 * @property RolesTable|\Cake\ORM\Association\BelongsTo $Roles
 *
 * @method SchoolUser get($primaryKey, $options = [])
 * @method SchoolUser newEmptyEntity($data = null, array $options = [])
 * @method SchoolUser[] newEntities(array $data, array $options = [])
 * @method SchoolUser|bool save(EntityInterface $entity, $options = [])
 * @method SchoolUser patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method SchoolUser[] patchEntities($entities, array $data, array $options = [])
 * @method SchoolUser findOrCreate($search, callable $callback = null, $options = [])
 */
class SchoolUsersTable extends Table
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

        $this->setTable('school_users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Schools', [
            'foreignKey' => 'school_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
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
        $rules->add($rules->existsIn(['school_id'], 'Schools'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['role_id'], 'Roles'));

        return $rules;
    }
}
