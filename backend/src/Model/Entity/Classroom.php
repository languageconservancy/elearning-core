<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Classroom Entity
 *
 * @property int $id
 * @property string $name
 * @property int $level_id
 * @property string $teacher_message
 * @property \Cake\I18n\FrozenDate $start_date
 * @property \Cake\I18n\FrozenDate $end_date
 * @property int $created_by
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Level $level
 * @property \App\Model\Entity\ClassroomUser[] $classroom_users
 * @property \App\Model\Entity\ClassroomLevelUnits[] $classroom_level_units
 */
class Classroom extends Entity
{
    /**
     * Fields that can be mass assigned using newEmptyEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        'name' => true,
        'school_id' => true,
        'level_id' => true,
        'teacher_message' => true,
        'start_date' => true,
        'end_date' => true,
        'created_by' => true,
        'created' => true,
        'modified' => true,
        'level' => true,
        'classroom_users' => true,
        'classroom_level_units' => true
    ];
}
