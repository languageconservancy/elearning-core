<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ClassroomLevelUnit Entity
 *
 * @property int $id
 * @property int $level_units_id
 * @property int $classroom_id
 * @property int $optional
 * @property int $active
 * @property int $no_repeat
 * @property \Cake\I18n\FrozenDate $release_date
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\LevelUnit $level_unit
 * @property \App\Model\Entity\Classroom $classroom
 */
class ClassroomLevelUnit extends Entity
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
        'level_units_id' => true,
        'classroom_id' => true,
        'optional' => true,
        'active' => true,
        'no_repeat' => true,
        'release_date' => true,
        'created' => true,
        'modified' => true,
        'level_unit' => true,
        'classroom' => true
    ];
}
