<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * UserPoints Entity
 *
 * @property int $id
 * @property int $user_id
 * @property float $path_score
 * @property float $review_score
 * @property float $social_score
 * @property float $reading_score
 * @property float $writing_score
 * @property float $speaking_score
 * @property float $listening_score
 * @property float $total_score
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\User $user
 */
class UserPoints extends Entity
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
