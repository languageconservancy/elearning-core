<?php

namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Schools Model
 *
 * @property \App\Model\Table\SchoolUsersTable|\Cake\ORM\Association\HasMany $SchoolUsers
 *
 * @method \App\Model\Entity\School get($primaryKey, $options = [])
 * @method \App\Model\Entity\School newEmptyEntity($data = null, array $options = [])
 * @method \App\Model\Entity\School[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\School|bool save(EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\School patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\School[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\School findOrCreate($search, callable $callback = null, $options = [])
 */
class SchoolsTable extends Table
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

        $this->setTable('schools');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->belongsTo('Files', [
            'foreignKey' => 'image_id',
        ]);
        // Make school users dependent, so if a school is deleted,
        // its school users are deleted automatically.
        $this->hasMany('SchoolUsers', [
            'foreignKey' => 'school_id',
            'dependent' => true
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
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
            ->scalar('grade_low')
            ->maxLength('grade_low', 255)
            ->allowEmptyString('grade_low');

        $validator
            ->scalar('grade_high')
            ->maxLength('grade_high', 255)
            ->requirePresence('grade_high', 'create')
            ->notEmptyString('grade_high');

        return $validator;
    }
}
