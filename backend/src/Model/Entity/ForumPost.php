<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class ForumPost extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];

    protected $_virtual = [
        'replycounter',
        'viewer_count'
    ];

    protected function _getreplycounter(): ?int
    {
        $ForumPost = TableRegistry::getTableLocator()->get('ForumPosts');
        return $ForumPost->find('all', [
            'conditions' => array(
                'parent_id' => $this->get('id'),
                'is_hide' => 'N'
            )
        ])->count();
    }

    protected function _getviewer_count(): ?int
    {
        $ForumPost = TableRegistry::getTableLocator()->get('ForumPostViewers');
        return $ForumPost->find('all', [
            'conditions' => array('post_id' => $this->get('id'))
        ])->count();
    }
}
