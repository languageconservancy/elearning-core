<?php

namespace App\Model\Entity;

use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;

/**
 * UserActivity Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string $activity_type
 * @property string $activity_id
 * @property string $points
 * @property FrozenTime $created
 * @property FrozenTime $modified
 *
 * @property User $user
 * @property UserActivity $activity
 */
class UserActivity extends Entity
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
        '*' => true,
        'id' => false,
    ];
}
