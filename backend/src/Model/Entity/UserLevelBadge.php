<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * UserLevelBadge Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $level_id
 * @property int $path_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Level $level
 * @property \App\Model\Entity\Learningpath $learningpath
 */
class UserLevelBadge extends Entity
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
        'user_id' => true,
        'level_id' => true,
        'path_id' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'level' => true,
        'learningpath' => true
    ];
}
