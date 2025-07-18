<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ClassroomUser Entity
 *
 * @property int $id
 * @property int $classroom_id
 * @property int $user_id
 * @property int $role_id
 *
 * @property \App\Model\Entity\Classroom $classroom
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Role $role
 */
class ClassroomUser extends Entity
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
        'classroom_id' => true,
        'user_id' => true,
        'role_id' => true,
        'classroom' => true,
        'user' => true,
        'role' => true
    ];
}
