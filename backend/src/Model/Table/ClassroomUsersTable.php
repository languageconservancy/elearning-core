<?php

namespace App\Model\Table;

use App\Model\Entity\ClassroomUser;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ClassroomUsers Model
 *
 * @property ClassroomsTable|BelongsTo $Classrooms
 * @property UsersTable|BelongsTo $Users
 * @property RolesTable|BelongsTo $Roles
 *
 * @method ClassroomUser get($primaryKey, $options = [])
 * @method ClassroomUser newEmptyEntity($data = null, array $options = [])
 * @method ClassroomUser[] newEntities(array $data, array $options = [])
 * @method ClassroomUser|bool save(EntityInterface $entity, $options = [])
 * @method ClassroomUser patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method ClassroomUser[] patchEntities($entities, array $data, array $options = [])
 * @method ClassroomUser findOrCreate($search, callable $callback = null, $options = [])
 */
class ClassroomUsersTable extends Table
{
    /**
     * Initialize method
     * @group ignore
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('classroom_users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Classrooms', [
            'foreignKey' => 'classroom_id',
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
        $rules->add($rules->existsIn(['classroom_id'], 'Classrooms'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['role_id'], 'Roles'));

        return $rules;
    }
}
