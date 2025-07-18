<?php

namespace App\Model\Table;

use App\Model\Entity\LessonFrameBlock;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * LessonFrameBlocks Model
 *
 * @property \App\Model\Table\LessonFramesTable|BelongsTo $LessonFrames
 * @property CardTable|BelongsTo $Cards
 * @property RecordingAudiosTable|BelongsTo $Audios
 *
 * @method LessonFrameBlock get($primaryKey, $options = [])
 * @method LessonFrameBlock newEmptyEntity($data = null, array $options = [])
 * @method LessonFrameBlock[] newEntities(array $data, array $options = [])
 * @method LessonFrameBlock|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method LessonFrameBlock patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method LessonFrameBlock[] patchEntities($entities, array $data, array $options = [])
 * @method LessonFrameBlock findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin TimestampBehavior
 */
class LessonFrameBlocksTable extends Table
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

        $this->setTable('lesson_frame_blocks');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

//      $this->belongsTo('LessonFrames', [
//          'foreignKey' => 'lesson_frame_id'
//      ]);
//

//      $this->belongsTo('Cards', [
//          'foreignKey' => 'card_id'
//      ]);
//      $this->belongsTo('Images', [
//          'foreignKey' => 'image_id'
//      ]);
//      $this->belongsTo('Videos', [
//          'foreignKey' => 'video_id'
//      ]);
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
            ->scalar('type')
            ->maxLength('type', 255)
            ->allowEmptyString('type');


        return $validator;
    }
}
