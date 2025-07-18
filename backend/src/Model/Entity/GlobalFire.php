<?php

namespace App\Model\Entity;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;

/**
 * GlobalFire Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $fire_days
 * @property int $streak_days
 * @property FrozenDate $last_day
 * @property FrozenTime $created
 * @property FrozenTime $modified
 *
 * @property User $user
 */
class GlobalFire extends Entity
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
        'fire_days' => true,
        'streak_days' => true,
        'last_day' => true,
        'created' => true,
        'modified' => true,
        'user' => true
    ];
}
