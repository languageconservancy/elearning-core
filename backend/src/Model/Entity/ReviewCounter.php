<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ReviewCounter Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $level_id
 * @property int $unit_id
 * @property int $counter
 * @property int $created
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Level $level
 * @property \App\Model\Entity\Unit $unit
 */
class ReviewCounter extends Entity
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
        'id' => false
    ];
}
