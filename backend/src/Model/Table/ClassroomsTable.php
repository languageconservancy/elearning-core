<?php

namespace App\Model\Table;

use App\Model\Entity\Classroom;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Classrooms Model
 *
 * @property LevelsTable|\Cake\ORM\Association\BelongsTo $Levels
 * @property \App\Model\Table\$SchoolsTable|\Cake\ORM\Association\BelongsTo $Schools
 * @property ClassroomUsersTable|HasMany $ClassroomUsers
 * @property ClassroomLevelUnitsTable|HasMany $ClassroomLevelUnits
 *
 * @method Classroom get($primaryKey, $options = [])
 * @method Classroom newEmptyEntity($data = null, array $options = [])
 * @method Classroom[] newEntities(array $data, array $options = [])
 * @method Classroom|bool save(EntityInterface $entity, $options = [])
 * @method Classroom patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method Classroom[] patchEntities($entities, array $data, array $options = [])
 * @method Classroom findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin TimestampBehavior
 */
class ClassroomsTable extends Table
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

        $this->setTable('classrooms');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created' => 'new',
                    'modified' => 'always',
                ]
            ]
        ]);

        $this->belongsTo('Levels', [
            'foreignKey' => 'level_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Schools', [
            'foreignKey' => 'school_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('ClassroomUsers', [
            'foreignKey' => 'classroom_id'
        ]);
        $this->hasMany('ClassroomLevelUnits', [
            'foreignKey' => 'classroom_id'
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
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->scalar('teacher_message')
            ->maxLength('teacher_message', 255)
            ->allowEmptyString('teacher_message');

        $validator
            ->integer('created_by')
            ->requirePresence('created_by', 'create')
            ->notEmptyDatetime('created_by');

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
        $rules->add($rules->existsIn(['level_id'], 'Levels'));
        $rules->add($rules->existsIn(['school_id'], 'Schools'));

        return $rules;
    }
}
