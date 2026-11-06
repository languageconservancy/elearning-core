<?php

declare(strict_types=1);

use Migrations\AbstractMigration;

class AddVocabBuilderTargetMode extends AbstractMigration
{
    public function change(): void
    {
        $this->table('exercises')
            ->addColumn('vbl_mode', 'string', ['limit' => 10, 'null' => true])
            ->update();
    }
}
