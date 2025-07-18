<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Exercise extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
