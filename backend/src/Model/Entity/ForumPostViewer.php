<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ForumPostViewer Entity
 *
 * @property int $id
 * @property int $post_id
 * @property int $user_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\ForumPost $forum_post
 * @property \App\Model\Entity\User $user
 */
class ForumPostViewer extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
