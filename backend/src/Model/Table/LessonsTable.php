<?php

namespace App\Model\Table;

use App\Model\Entity\Lesson;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Lessons Model
 *
 * @property \App\Model\Table\LessonFramesTable|\Cake\ORM\Association\HasMany $LessonFrames
 *
 * @method Lesson get($primaryKey, $options = [])
 * @method Lesson newEmptyEntity($data = null, array $options = [])
 * @method Lesson[] newEntities(array $data, array $options = [])
 * @method Lesson|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method Lesson patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method Lesson[] patchEntities($entities, array $data, array $options = [])
 * @method Lesson findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class LessonsTable extends Table
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

        $this->setTable('lessons');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Lessonframes', [
            'foreignKey' => 'lesson_id',
            'className' => 'LessonFrames',
            'dependent' => true,
            'sort' => array('frameorder' => 'ASC'),
            'cascadeCallbacks' => true
        ]);

        $this->belongsToMany('Units', [
            'through' => 'Unitdetails'
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

        return $validator
            ->notEmptyString('name', 'Please enter Name')
            ->add('name', 'id', [
                'rule' => function ($value, $context) {
                    if (!isset($context['data']['id'])) {
                        $count = $this
                            ->find()
                            ->where(['Lessons.name' => $context['data']['name']])
                            ->count();
                    } else {
                        $count = $this
                            ->find()
                            ->where(['Lessons.name' => $context['data']['name']])
                            ->where(['Lessons.id !=' => $context['data']['id']])
                            ->count();
                    }

                    return $count === 0;
                },
                'message' => 'Lesson name is already exists.'
            ]);
    }
}
