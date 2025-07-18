<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Content extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
