<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

class ExerciseCustomOption extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
