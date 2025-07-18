<?php

namespace App\Model\Table;

use App\Model\Entity\ReviewQueue;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;

/**
 * ReviewQueues Model
 *
 * @property UsersTable|BelongsTo $Users
 * @property CardTable|BelongsTo $Cards
 *
 * @method ReviewQueue get($primaryKey, $options = [])
 * @method ReviewQueue newEmptyEntity($data = null, array $options = [])
 * @method ReviewQueue[] newEntities(array $data, array $options = [])
 * @method ReviewQueue|bool save(EntityInterface $entity, $options = [])
 * @method ReviewQueue patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method ReviewQueue[] patchEntities($entities, array $data, array $options = [])
 * @method ReviewQueue findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ReviewQueuesTable extends Table
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

        $this->setTable('review_queues');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);
        $this->belongsTo('Cards', [
            'foreignKey' => 'card_id'
        ]);

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created' => 'new',
                    'modified' => 'always',
                ]
            ]
        ]);
        //$this->loadModel('ReviewVars');
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
//            ->scalar('skill_type')
//            ->allowEmptyString('skill_type');
//
//        $validator
//            ->numeric('xp_1')
//            ->requirePresence('xp_1', 'create')
//            ->notEmpty('xp_1');
//
//        $validator
//            ->numeric('xp_2')
//            ->requirePresence('xp_2', 'create')
//            ->notEmpty('xp_2');
//
//        $validator
//            ->numeric('xp_3')
//            ->requirePresence('xp_3', 'create')
//            ->notEmpty('xp_3');
//
//        $validator
//            ->numeric('xp_4')
//            ->requirePresence('xp_4', 'create')
//            ->notEmpty('xp_4');
//
//        $validator
//            ->integer('sort')
//            ->requirePresence('sort', 'create')
//            ->notEmpty('sort');
//
//        $validator
//            ->integer('num_times')
//            ->requirePresence('num_times', 'create')
//            ->notEmpty('num_times');
//
//        $validator
//            ->dateTime('daystamp')
//            ->requirePresence('daystamp', 'create')
//            ->notEmpty('daystamp');

        return $validator;
    }

    /**
     * Updates the sort value of the review card before finally saving it to the table in the database.
     * There's a different card for each type of skill for each card, for each user.
     * Take the average of the last four cards and use it in the spaced repetition algorithm along
     * with the days since the card was last presented to the user and the number of times the user
     * has seen the card.
     * @param $event type of table event (save, post, patch, etc)
     * @param $entity ReviewQueue entity
     */
    public function beforeSave(\Cake\Event\EventInterface $event, $entity, $options)
    {
        // Get review variables, which are global so there are only a few numbers
        $ReviewVars = TableRegistry::getTableLocator()->get('ReviewVars');
        $sortEqMultipliers = $ReviewVars->find('list', array('keyField' => 'key', 'valueField' => 'value'))->toArray();
        $counter = 0;
        $total = 0;
        $points = array($entity->xp_1, $entity->xp_2, $entity->xp_3, $entity->xp_4);
        // If there's a point value for an XP, add it to the total, and increment the counter, which tracks
        // the number of point values added.
        foreach ($points as $point) {
            if ($point != '') {
                $total += $point;
                $counter++;
            }
        }
        // Get the average points per XP point value. 0 if there were none.
        $xp_avg = $counter == 0 ? 0 : ($total / $counter);

        // Compute the sort order of this review card using the equation
        // sort = a * (average_points) + b * (last time review) + c * (num times reviewed)
        // The higher the sort value, the longer it will take for the user to see the card again during review session.
        $sort = ($sortEqMultipliers['a'] * $xp_avg)
            + ($sortEqMultipliers['b'] * $entity->daystamp)
            + ($sortEqMultipliers['c'] * $entity->num_times);

        // Update the sort value of the review card entity,
        // which determines when it will appear next compared to the other cards
        $entity->sort = round($sort);
    }
}
