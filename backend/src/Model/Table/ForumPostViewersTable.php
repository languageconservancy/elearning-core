<?php

namespace App\Model\Table;

use App\Model\Entity\ForumPostViewer;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ForumPostViewers Model
 *
 * @property ForumPostsTable|BelongsTo $ForumPosts
 * @property UsersTable|BelongsTo $Users
 *
 * @method ForumPostViewer get($primaryKey, $options = [])
 * @method ForumPostViewer newEmptyEntity($data = null, array $options = [])
 * @method ForumPostViewer[] newEntities(array $data, array $options = [])
 * @method ForumPostViewer|bool save(EntityInterface $entity, $options = [])
 * @method ForumPostViewer patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method ForumPostViewer[] patchEntities($entities, array $data, array $options = [])
 * @method ForumPostViewer findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ForumPostViewersTable extends Table
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

        $this->setTable('forum_post_viewers');
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
        $this->belongsTo('ForumPosts', [
            'foreignKey' => 'post_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
        $rules->add($rules->existsIn(['post_id'], 'ForumPosts'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        return $rules;
    }
}
