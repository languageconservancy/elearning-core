<?php

namespace App\Model\Table;

use App\Model\Entity\ClassroomLevelUnit;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ClassroomLevelUnits Model
 *
 * @property LevelUnitsTable|BelongsTo $LevelUnits
 * @property ClassroomsTable|BelongsTo $Classrooms
 *
 * @method ClassroomLevelUnit get($primaryKey, $options = [])
 * @method ClassroomLevelUnit newEmptyEntity($data = null, array $options = [])
 * @method ClassroomLevelUnit[] newEntities(array $data, array $options = [])
 * @method ClassroomLevelUnit|bool save(EntityInterface $entity, $options = [])
 * @method ClassroomLevelUnit patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method ClassroomLevelUnit[] patchEntities($entities, array $data, array $options = [])
 * @method ClassroomLevelUnit findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ClassroomLevelUnitsTable extends Table
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

        $this->setTable('classroom_level_units');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('LevelUnits', [
            'foreignKey' => 'level_units_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Classrooms', [
            'foreignKey' => 'classroom_id',
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

        $validator
            ->requirePresence('level_units_id', 'create')
            ->notEmptyString('level_units_id');

        $validator
            ->requirePresence('classroom_id', 'create')
            ->notEmptyString('classroom_id');

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
        $rules->add($rules->existsIn(['level_units_id'], 'LevelUnits'));
        $rules->add($rules->existsIn(['classroom_id'], 'Classrooms'));

        return $rules;
    }
}
