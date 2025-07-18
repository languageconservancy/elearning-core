<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Usersetting extends Entity
{
    use GeneralFunctionTrait;

    // Make all fields mass assignable except for primary key field "id".
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
    protected $_virtual = [
        'FullProfileImageUrl',
        'FormatedMotivationTime'
    ];

    protected function _getFullProfileImageUrl()
    {
        if ($this->get('profile_picture') != '') {
            return $this->_getLink($this->get('profile_picture'), $this->get('aws_profile_link'), 'PROFILE_IMAGE');
        } else {
            return null;
        }
    }

    protected function _getFormatedMotivationTime()
    {
        if ($this->get('motivation_time') == null) {
            return null;
        } else {
            $time = $this->get('motivation_time');
            return date("h:i:A", strtotime($time));
        }
    }
}
