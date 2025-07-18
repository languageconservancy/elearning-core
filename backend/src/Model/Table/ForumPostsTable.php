<?php

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class ForumPostsTable extends Table
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

        $this->setTable('forum_posts');
        $this->setDisplayField('title');
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

        $this->belongsTo('ParentForumPosts', [
            'className' => 'ForumPosts',
            'foreignKey' => 'parent_id'
        ]);
        $this->belongsTo('Forums', [
            'foreignKey' => 'forum_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);

        $this->belongsTo('audioDetails', [
            'className' => 'RecordingAudios',
            'foreignKey' => 'audio',
        ]);

        $this->belongsTo('ForumFlags', [
            'foreignKey' => 'flag_id'
        ]);
        $this->hasMany('ChildForumPosts', [
            'className' => 'ForumPosts',
            'foreignKey' => 'parent_id',
            'sort' => ['ChildForumPosts.created' => 'ASC']
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
            ->scalar('title')
            ->maxLength('title', 500)
            ->allowEmptyString('title', 'Please enter content');

        $validator
            ->scalar('content')
            ->allowEmptyString('content', 'Please enter content');

        $validator
            ->integer('audio')
            ->allowEmptyString('audio');

        $validator
            ->requirePresence('forum_id', 'create')
            ->notEmptyString('forum_id', 'create');

        $validator
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id', 'create');
        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): \Cake\ORM\RulesChecker
    {
        $rules->add($rules->existsIn(['parent_id'], 'ParentForumPosts'));
        $rules->add($rules->existsIn(['forum_id'], 'Forums'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['flag_id'], 'ForumFlags'));
        return $rules;
    }
}
