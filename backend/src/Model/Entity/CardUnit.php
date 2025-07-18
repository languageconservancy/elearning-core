<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CardUnit Entity
 *
 * @property int $id
 * @property int $card_id
 * @property int $unit_id
 *
 * @property Card $card
 * @property Unit $unit
 */
class CardUnit extends Entity
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
        'card_id' => true,
        'unit_id' => true,
        'card' => true,
        'unit' => true
    ];
}
