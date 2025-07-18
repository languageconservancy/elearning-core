<?php

namespace App\Model\Table;

use App\Model\Entity\LessonFrame;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * LessonFrames Model
 *
 * @property \App\Model\Table\LessonsTable|\Cake\ORM\Association\BelongsTo $Lessons
 * @property \App\Model\Table\AudiosTable|\Cake\ORM\Association\BelongsTo $Audios
 * @property \App\Model\Table\LessonFrameBlocksTable|\Cake\ORM\Association\HasMany $LessonFrameBlocks
 *
 * @method LessonFrame get($primaryKey, $options = [])
 * @method LessonFrame newEmptyEntity($data = null, array $options = [])
 * @method LessonFrame[] newEntities(array $data, array $options = [])
 * @method LessonFrame|bool save(EntityInterface $entity, $options = [])
 * @method LessonFrame patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method LessonFrame[] patchEntities($entities, array $data, array $options = [])
 * @method LessonFrame findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin TimestampBehavior
 */
class LessonFramesTable extends Table
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

        $this->setTable('lesson_frames');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Lessons', [
            'foreignKey' => 'lesson_id',
            'joinType' => 'INNER'
        ]);

        $this->hasMany('LessonFrameBlocks', [
            'foreignKey' => 'lesson_frame_id',
            'dependent' => true,
            'cascadeCallbacks' => true
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
//      $validator->requirePresence([
//          'number_of_block' => [
//              'mode' => 'update',
//              'message' => 'The number_of_block is required.',
//          ]
//      ]);
        return $validator
            ->notEmptyString('number_of_block', 'Body cannot be empty', 'update')
            ->allowEmptyString('number_of_block', 'create')
            ->add('name', 'id', [
                'rule' => function ($value, $context) {
                    if (!isset($context['data']['id'])) {
                        $count = $this->find()
                            ->where(['LessonFrames.name' => $context['data']['name']])
                            ->where(['LessonFrames.lesson_id' => $context['data']['lesson_id']])
                            ->count();
                    } else {
                        $count = $this->find()
                            ->where(['LessonFrames.name' => $context['data']['name']])
                            ->where(['LessonFrames.lesson_id' => $context['data']['lesson_id']])
                            ->where(['LessonFrames.id !=' => $context['data']['id']])
                            ->count();
                    }
                    return $count === 0;
                },
                'message' => 'Frame name is already exists.'
            ]);
    }
}
