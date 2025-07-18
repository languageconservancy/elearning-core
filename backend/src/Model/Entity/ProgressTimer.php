<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ProgressTimer Entity
 *
 * @property int $id
 * @property int $path_id
 * @property int $level_id
 * @property int $user_id
 * @property int $unit_id
 * @property string $timer_type
 * @property int $minute_spent
 * @property \Cake\I18n\FrozenDate $entry_date
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Learningpath $learningpath
 * @property \App\Model\Entity\Level $level
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Unit $unit
 */
class ProgressTimer extends Entity
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
