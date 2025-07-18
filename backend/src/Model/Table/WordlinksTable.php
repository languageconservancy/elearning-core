<?php

namespace App\Model\Table;

use App\Model\Entity\Wordlink;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Wordlinks Model
 *
 * @property \App\Model\Table\ClassroomsTable|\Cake\ORM\Association\BelongsTo $Classrooms
 * @property \App\Model\Table\SchoolsTable|\Cake\ORM\Association\BelongsTo $Schools
 *
 * @method Wordlink get($primaryKey, $options = [])
 * @method Wordlink newEmptyEntity($data = null, array $options = [])
 * @method Wordlink[] newEntities(array $data, array $options = [])
 * @method Wordlink|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method Wordlink patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method Wordlink[] patchEntities($entities, array $data, array $options = [])
 * @method Wordlink findOrCreate($search, callable $callback = null, $options = [])
 */
class WordlinksTable extends Table
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

        $this->setTable('wordlinks');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Classrooms', [
            'foreignKey' => 'classroom_id'
        ]);
        $this->belongsTo('Schools', [
            'foreignKey' => 'school_id'
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
            ->scalar('wordlink')
            ->maxLength('wordlink', 30)
            ->requirePresence('wordlink', 'create')
            ->notEmptyString('wordlink');

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
        $rules->add($rules->existsIn(['school_id'], 'Schools'));

        return $rules;
    }
}
