<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Friends extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
