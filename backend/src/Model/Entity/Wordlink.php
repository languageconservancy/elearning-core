<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Wordlink Entity
 *
 * @property int $id
 * @property string $wordlink
 * @property int $classroom_id
 * @property int $school_id
 *
 * @property \App\Model\Entity\Classroom $classroom
 * @property \App\Model\Entity\School $school
 */
class Wordlink extends Entity
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
        'wordlink' => true,
        'classroom_id' => true,
        'school_id' => true,
        'classroom' => true,
        'school' => true
    ];
}
