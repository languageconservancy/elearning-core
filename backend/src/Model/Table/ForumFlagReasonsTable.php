<?php

namespace App\Model\Table;

use App\Model\Entity\ForumFlagReason;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ForumFlagReasons Model
 *
 * @method ForumFlagReason get($primaryKey, $options = [])
 * @method ForumFlagReason newEmptyEntity($data = null, array $options = [])
 * @method ForumFlagReason[] newEntities(array $data, array $options = [])
 * @method ForumFlagReason|bool save(EntityInterface $entity, $options = [])
 * @method ForumFlagReason patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method ForumFlagReason[] patchEntities($entities, array $data, array $options = [])
 * @method ForumFlagReason findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin TimestampBehavior
 */
class ForumFlagReasonsTable extends Table
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
        $this->setTable('forum_flag_reasons');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created' => 'new',
                    'entry_time' => 'new',
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

        $validator
            ->scalar('reason')
            ->maxLength('reason', 255)
            ->requirePresence('reason', 'create')
            ->notEmptyString('reason');

        return $validator;
    }
}
