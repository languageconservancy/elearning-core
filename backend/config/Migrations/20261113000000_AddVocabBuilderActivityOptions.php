<?php

declare(strict_types=1);

use Migrations\AbstractMigration;

class AddVocabBuilderActivityOptions extends AbstractMigration
{
    public function change(): void
    {
        $this->table('exercises')
            ->addColumn('vbl_variant', 'string', ['limit' => 10, 'null' => true])
            ->update();
        $this->table('exercise_options')
            ->addColumn('vbl_alignment', 'text', ['null' => true])
            ->update();
    }
}
