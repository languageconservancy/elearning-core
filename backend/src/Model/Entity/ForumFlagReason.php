<?php

namespace App\Model\Entity;

use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;

/**
 * ForumFlagReason Entity
 *
 * @property int $id
 * @property string $reason
 * @property FrozenTime $created
 * @property FrozenTime $modified
 */
class ForumFlagReason extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
