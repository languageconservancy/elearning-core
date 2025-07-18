<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ReviewQueue Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $card_id
 * @property string $skill_type
 * @property float $xp_1
 * @property float $xp_2
 * @property float $xp_3
 * @property float $xp_4
 * @property int $sort
 * @property int $num_times
 * @property \Cake\I18n\FrozenTime $daystamp
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Card $card
 */
class ReviewQueue extends Entity
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
    protected $_virtual = [
        'xp_avg',
    ];

    protected function _getXp_avg()
    {
        $counter = 0;
        $total = 0;
        if ($this->get('xp_1') != '') {
            $total = $total + $this->get('xp_1');
            $counter++;
        }
        if ($this->get('xp_2') != '') {
            $total = $total + $this->get('xp_2');
            $counter++;
        }
        if ($this->get('xp_3') != '') {
            $total = $total + $this->get('xp_3');
            $counter++;
        }
        if ($this->get('xp_4') != '') {
            $total = $total + $this->get('xp_4');
            $counter++;
        }
        if ($counter != 0) {
            $xp_avg = $total / $counter;
        } else {
            $xp_avg = 0;
        }
        return $xp_avg;
    }
}
