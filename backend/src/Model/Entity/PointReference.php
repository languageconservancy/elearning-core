<?php

namespace App\Model\Entity;

use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;

/**
 * PointReference Entity
 *
 * @property int $id
 * @property float $prompt_type
 * @property float $response_type
 * @property float $exercise_type
 * @property float $reading_pts
 * @property float $writing_pts
 * @property float $listening_pts
 * @property float $speaking_pts
 * @property FrozenTime $created
 * @property FrozenTime $modified
 */
class PointReference extends Entity
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
