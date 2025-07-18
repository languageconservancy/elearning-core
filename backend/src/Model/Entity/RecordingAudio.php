<?php

namespace App\Model\Entity;

use Cake\Core\Configure;
use Cake\ORM\Entity;

class RecordingAudio extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
    protected $_virtual = [
        'link',
        'aws_link'
    ];

    protected function _getlink()
    {
        if ($this->get('aws_link') != null && $this->get('aws_link') != '') {
            $path = $this->get('aws_link');
        } else {
            $path = Configure::read('ADMIN_LINK') . 'img/RecordingAudio/' . $this->get('file_name');
        }
        return $path;
    }

    protected function _getaws_link()
    {
        if ($this->get('aws_link') != null && $this->get('aws_link') != '') {
            $path = $this->get('aws_link');
        } else {
            $path = null;
        }
        return $path;
    }
}
