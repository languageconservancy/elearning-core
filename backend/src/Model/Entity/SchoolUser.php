<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SchoolUser Entity
 *
 * @property int $id
 * @property int $school_id
 * @property int $user_id
 * @property int $role_id
 *
 * @property \App\Model\Entity\School $school
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Role $role
 */
class SchoolUser extends Entity
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
        'user_id' => true,
        'f_name' => true,
        'l_name' => true,
        'role_id' => true,
        'school' => true,
        'user' => true,
        'role' => true
    ];
}
