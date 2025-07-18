<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Userimage extends Entity
{
    use GeneralFunctionTrait;

    // Make all fields mass assignable except for primary key field "id".
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
    protected $_virtual = [
        'FullImageUrl'
    ];

    protected function _getFullImageUrl()
    {
        return GeneralFunctionTrait::_getLink($this->get('image'), $this->get('aws_link'), 'GALLERYIMAGE');
    }
}
