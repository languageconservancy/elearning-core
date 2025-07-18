<?php

namespace App\Model\Table;

use App\Model\Entity\UserUnitActivity;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UserUnitActivities Model
 *
 * @property LearningpathsTable|BelongsTo $Learningpaths
 * @property LevelsTable|BelongsTo $Levels
 * @property UnitsTable|BelongsTo $Units
 * @property UsersTable|BelongsTo $Users
 *
 * @method UserUnitActivity get($primaryKey, $options = [])
 * @method UserUnitActivity newEmptyEntity($data = null, array $options = [])
 * @method UserUnitActivity[] newEntities(array $data, array $options = [])
 * @method UserUnitActivity|bool save(EntityInterface $entity, $options = [])
 * @method UserUnitActivity patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method UserUnitActivity[] patchEntities($entities, array $data, array $options = [])
 * @method UserUnitActivity findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin TimestampBehavior
 */
class UserUnitActivitiesTable extends Table
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

        $this->setTable('user_unit_activities');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Learningpaths', [
            'foreignKey' => 'path_id'
        ]);
        $this->belongsTo('Levels', [
            'foreignKey' => 'level_id'
        ]);
        $this->belongsTo('Units', [
            'foreignKey' => 'unit_id'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
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

        return $validator;
    }
}
