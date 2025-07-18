<?php

namespace App\Model\Table;

use App\Model\Entity\ProgressTimer;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProgressTimers Model
 *
 * @property LearningpathsTable|BelongsTo $Learningpaths
 * @property LevelsTable|BelongsTo $Levels
 * @property UsersTable|BelongsTo $Users
 * @property UnitsTable|BelongsTo $Units
 *
 * @method ProgressTimer get($primaryKey, $options = [])
 * @method ProgressTimer newEmptyEntity($data = null, array $options = [])
 * @method ProgressTimer[] newEntities(array $data, array $options = [])
 * @method ProgressTimer|bool save(EntityInterface $entity, $options = [])
 * @method ProgressTimer patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method ProgressTimer[] patchEntities($entities, array $data, array $options = [])
 * @method ProgressTimer findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ProgressTimersTable extends Table
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

        $this->setTable('progress_timers');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Learningpaths', [
            'foreignKey' => 'path_id'
        ]);
        $this->belongsTo('Levels', [
            'foreignKey' => 'level_id'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);
        $this->belongsTo('Units', [
            'foreignKey' => 'unit_id'
        ]);

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created' => 'new',
                    'modified' => 'always',
                ]
            ]
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


//        $validator
//            ->scalar('timer_type')
//            ->allowEmptyString('timer_type');
//
//        $validator
//            ->integer('minute_spent')
//            ->allowEmptyString('minute_spent');
//
//        $validator
//            ->date('entry_date')
//            ->allowEmptyDate('entry_date');

        return $validator;
    }
}
