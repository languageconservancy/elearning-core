<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class ClassStudentsTable extends Table
{
    public function initialize(array $config): void
    {

        $this->setTable('class_students');
        $this->setPrimaryKey('id');
    }
}
