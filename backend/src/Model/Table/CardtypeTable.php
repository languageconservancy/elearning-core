<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class CardtypeTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('card_types');
        $this->setPrimaryKey('id');
    }

    /**
     * Get card types in a simple array of values
     */
    public function getTypes(): array
    {
        $types = $this->find('list', [
            'keyField' => 'id',
            'valueField' => 'title'
        ])->toArray();
        return $types;
    }
}
