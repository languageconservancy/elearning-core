<?php

declare(strict_types=1);

use Migrations\AbstractMigration;

class AddVocabBuilderExercises extends AbstractMigration
{
    public function change(): void
    {
        $this->table('exercises')
            ->addColumn('vbl_activity', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('vbl_review_enabled', 'boolean', ['default' => false])
            ->update();
        $this->table('exercise_options')
            ->addColumn('vbl_letter', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('vbl_letter_audio_id', 'integer', ['null' => true])
            ->addColumn('vbl_letter_image_id', 'integer', ['null' => true])
            ->addColumn('vbl_coloring_image_id', 'integer', ['null' => true])
            ->update();
    }
}
