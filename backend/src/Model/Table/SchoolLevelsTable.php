<?php

namespace App\Model\Table;

use App\Model\Entity\SchoolLevel;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SchoolLevels Model
 *
 * @property SchoolsTable|BelongsTo $Schools
 * @property LevelsTable|BelongsTo $Levels
 * @property UsersTable|BelongsTo $Users
 *
 * @method SchoolLevel get($primaryKey, $options = [])
 * @method SchoolLevel newEmptyEntity($data = null, array $options = [])
 * @method SchoolLevel[] newEntities(array $data, array $options = [])
 * @method SchoolLevel|bool save(EntityInterface $entity, $options = [])
 * @method SchoolLevel patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method SchoolLevel[] patchEntities($entities, array $data, array $options = [])
 * @method SchoolLevel findOrCreate($search, callable $callback = null, $options = [])
 */
class SchoolLevelsTable extends Table
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

        $this->setTable('school_levels');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Schools', [
            'foreignKey' => 'school_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Levels', [
            'foreignKey' => 'level_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'owner_id',
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
        $rules->add($rules->existsIn(['level_id'], 'Levels'));
        $rules->add($rules->existsIn(['owner_id'], 'Users'));

        return $rules;
    }
}
