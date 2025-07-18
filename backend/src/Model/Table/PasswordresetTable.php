<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class PasswordresetTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('passwordreset');
        $this->setPrimaryKey('id');
    }
}
