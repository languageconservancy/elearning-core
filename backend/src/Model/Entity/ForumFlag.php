<?php

namespace App\Model\Entity;

use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;

/**
 * ForumFlag Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $post_id
 * @property string $flag
 * @property FrozenTime $entry_time
 * @property FrozenTime $created
 * @property FrozenTime $modified
 *
 * @property User $user
 * @property ForumPost $forum_post
 */
class ForumFlag extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
