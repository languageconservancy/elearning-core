<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\Event;
use ArrayObject;

class UsersTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('users');
        $this->setPrimaryKey('id');
        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id'
        ]);

        $this->hasMany('Friends', [
            'className' => 'Friends'
        ]);


        $this->belongsTo('Learningspeed', [
            'foreignKey' => 'learningspeed_id'
        ]);
        $this->belongsTo('Learningpaths', [
            'foreignKey' => 'learningpath_id'
        ]);

        $this->hasOne('Usersetting', [
            'foreignKey' => 'user_id',
            'className' => 'Usersettings',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);

        $this->hasMany('Userimages', [
            'foreignKey' => 'user_id',
            'className' => 'Userimages',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);

        $this->hasMany('UserPoints', [
            'foreignKey' => 'user_id',
            'className' => 'UserPoints',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);

        $this->hasMany('UserUnitActivities', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('UserActivities', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ForumFlags', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('ForumPosts', [
            'foreignKey' => 'user_id'
        ]);

        $this->hasMany('Posts', [
            'className' => 'ForumPosts',
            'foreignKey' => 'user_id',
            'conditions' => array('parent_id IS' => null)
        ]);
        $this->hasMany('PostReply', [
            'className' => 'ForumPosts',
            'foreignKey' => 'user_id',
            'conditions' => array('parent_id IS NOT' => null)
        ]);

//        $this->hasMany('Friends')
//                ->setConditions(['User.idewqdfewf' => 'greger']);
//         $this->belongsTo('Friendship', [
//           'className' => 'Users',
//            'joinTable' => 'friends',
//            'foreignKey' => 'id',
//            'associationForeignKey' => 'friend_id'
//        ]);

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created' => 'new',
                    'registered' => 'new',
                    'modified' => 'always',
                ]
            ]
        ]);
    }

    public function beforeSave(Event $event, $entity, ArrayObject $options)
    {
        if ($entity->isNew() && !$entity->isDirty('dob')) {
            if (empty($this->approximate_age) && !empty($entity->dob)) {
                $entity->approximate_age = $this->calculateAge($entity->dob);
            }
        }
    }

    private function calculateAge($dob)
    {
        $dob = new \DateTime($dob);
        $today = new \DateTime();
        return $today->diff($dob)->y;
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->requirePresence([
            'email' => [
                'mode' => 'create',
                'message' => 'Please enter Email.',
            ],
            'password' => [
                'mode' => 'create',
                'message' => 'Please Enter Password.',
            ],
            'repassword' => [
                'mode' => 'create',
                'message' => 'Please Enter confirm Password.',
            ]
        ]);
        return $validator
            ->notEmptyString('email', 'Please enter Email.')
            ->notEmptyString('name', 'Please enter Name.')
            ->notEmptyString('role_id', 'Please enter Role')
            ->notEmptyString('learningpath_id', 'Please select Learning Path.')
            ->notEmptyString('learningspeed_id', 'Please select Learning Speed.')
            ->add('email', 'email', [
                'rule' => [$this, 'isUnique'],
                'message' => __('Email already registered.')
            ])
            ->add('password', 'repassword', [
                'rule' => function ($value, $context) {
                    return isset($context['data']['repassword']) && $context['data']['repassword'] === $value;
                },
                'message' => 'Please check password and confirm password.'
            ]);
    }

    public function validationIgnorePassword(Validator $validator): Validator
    {
        $validator->requirePresence([
            'email' => [
                'mode' => 'create',
                'message' => 'Please enter Email',
            ]
        ]);
        return $validator
            ->notEmptyString('email', 'Please enter Email')
            ->notEmptyString('name', 'Please enter Name')
            ->requirePresence(['role_id' => [
                'mode' => 'create',
                'message' => 'Please enter Role']])
            ->requirePresence(['learningpath_id' => [
                'mode' => 'create',
                'message' => 'Please select Learning Path']])
            ->requirePresence(['learningspeed_id' => [
                'mode' => 'create',
                'message' => 'Please select Learning Speed']])
            ->add('email', 'email', [
                'rule' => [$this, 'isUnique'],
                'message' => __('Email already registered')
            ]);
    }

    public function validationIgnoreEmailAndPassword(Validator $validator): Validator
    {
        return $validator
            ->notEmptyString('name', 'Please enter Name')
            ->requirePresence(['role_id' => [
                'mode' => 'create',
                'message' => 'Please enter Role']])
            ->requirePresence(['learningpath_id' => [
                'mode' => 'create',
                'message' => 'Please select Learning Path']])
            ->requirePresence(['learningspeed_id' => [
                'mode' => 'create',
                'message' => 'Please select Learning Speed']]);
    }

    public function isUnique($email): bool
    {
        $user = $this->find()
            ->where([
                'Users.email' => $email,
            ])
            ->first();
        if ($user) {
            return false;
        }
        return true;
    }
}
