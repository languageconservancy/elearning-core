<?php

namespace App\Model\Entity;

use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;

/**
 * Forum Entity
 *
 * @property int $id
 * @property int $path_id
 * @property int $level_id
 * @property int $unit_id
 * @property string $title
 * @property string $subtitle
 * @property FrozenTime $created
 * @property FrozenTime $modified
 *
 * @property Learningpath $learningpath
 * @property Levels $level
 * @property Units $unit
 * @property ForumPost[] $forum_posts
 */
class Forum extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
