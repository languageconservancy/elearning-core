<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class ContentsTable extends Table
{
    public function initialize(array $config): void
    {
        //$this->addBehavior('Timestamp'); //It will help to store created datetime in db

        $this->setTable('contents');
        $this->setPrimaryKey('id');
    }
}
