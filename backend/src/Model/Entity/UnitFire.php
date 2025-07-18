<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * UnitFire Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $unit_id
 * @property int $reading_persantage
 * @property int $writing_percentage
 * @property int $listening_percentage
 * @property int $speaking_percentage
 * @property int $total_persentage
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Unit $unit
 */
class UnitFire extends Entity
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
        'unit_id' => true,
        'reading_persantage' => true,
        'writing_percentage' => true,
        'listening_percentage' => true,
        'speaking_percentage' => true,
        'total_persentage' => true,
        'user' => true,
        'unit' => true
    ];
}
