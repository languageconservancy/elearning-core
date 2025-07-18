<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SchoolLevel Entity
 *
 * @property int $id
 * @property int $school_id
 * @property int $level_id
 * @property int $owner_id
 *
 * @property \App\Model\Entity\School $school
 * @property \App\Model\Entity\Level $level
 * @property \App\Model\Entity\User $user
 */
class SchoolLevel extends Entity
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
        'school_id' => true,
        'level_id' => true,
        'owner_id' => true,
        'school' => true,
        'level' => true,
        'user' => true
    ];
}
