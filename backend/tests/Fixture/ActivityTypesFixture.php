<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ActivityTypesFixture
 *
 */
class ActivityTypesFixture extends TestFixture
{
    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => [
            'type' => 'integer',
            'length' => 11,
            'unsigned' => false,
            'null' => false,
            'default' => null,
            'comment' => '',
            'precision' => null,
            'autoIncrement' => null
        ],
        'type' => [
            'type' => 'integer',
            'length' => 11,
            'unsigned' => false,
            'null' => false,
            'default' => null,
            'comment' => '',
            'precision' => null,
            'autoIncrement' => null
        ],
        'specific_skill' => [
            'type' => 'string',
            'length' => null,
            'null' => false,
            'default' => null,
            'collate' => 'utf8_general_ci',
            'comment' => 'Skill type according to Jan\'s document on google drive',
            'precision' => null,
            'fixed' => null
        ],
        'global_skill' => [
            'type' => 'string',
            'length' => null,
            'null' => false,
            'default' => null,
            'collate' => 'utf8_general_ci',
            'comment' => 'Skill type in software, since these are what ' .
                'exist in the database, like in ReviewQueues table',
            'precision' => null,
            'fixed' => null
        ],
        'prompt_response_pairs_words' => [
            'type' => 'string',
            'length' => null,
            'null' => false,
            'default' => null,
            'collate' => 'utf8_general_ci',
            'comment' => 'Which prompt-response pairs are valid for the activity type',
            'precision' => null,
            'fixed' => null
        ],
        'prompt_response_pairs_patterns' => [
            'type' => 'string',
            'length' => null,
            'null' => false,
            'default' => null,
            'collate' => 'utf8_unicode_ci',
            'comment' => '',
            'precision' => null,
            'fixed' => null
        ],
        'exercise_type_words' => [
            'type' => 'string',
            'length' => null,
            'null' => false,
            'default' => null,
            'collate' => 'utf8_general_ci',
            'comment' => 'Which exercise types are allowed for the activity type. ' .
                'These must correspond to point references table\'s exercise column',
            'precision' => null,
            'fixed' => null
        ],
        'exercise_type_patterns' => [
            'type' => 'string',
            'length' => null,
            'null' => false,
            'default' => null,
            'collate' => 'utf8_general_ci',
            'comment' => 'Exercise types for Pattern card activities',
            'precision' => null,
            'fixed' => null
        ],
        'learning_percentage_words' => [
            'type' => 'integer',
            'length' => 11,
            'unsigned' => false,
            'null' => false,
            'default' => null,
            'comment' => 'Percentage of word cards that will be presented to the' .
                ' user using this activity type during learning session exercises',
            'precision' => null,
            'autoIncrement' => null
        ],
        'learning_percentage_patterns' => [
            'type' => 'integer',
            'length' => 11,
            'unsigned' => false,
            'null' => false,
            'default' => null,
            'comment' => 'Percentage of pattern cards that will be presented to ' .
                'the user using this activity type during learning session exercise',
            'precision' => null,
            'autoIncrement' => null
        ],
        'review_percentage_words' => [
            'type' => 'integer',
            'length' => 11,
            'unsigned' => false,
            'null' => false,
            'default' => null,
            'comment' => 'Percentage of word cards that will be presented to the ' .
                'user using this activity type during review session exercises',
            'precision' => null,
            'autoIncrement' => null
        ],
        'review_percentage_patterns' => [
            'type' => 'integer',
            'length' => 11,
            'unsigned' => false,
            'null' => false,
            'default' => null,
            'comment' => 'Percentage of pattern cards that will be presented to ' .
                'the user using this activity type during review session exercises',
            'precision' => null,
            'autoIncrement' => null
        ],
        'learning_style' => [
            'type' => 'string',
            'length' => null,
            'null' => false,
            'default' => null,
            'collate' => 'utf8_general_ci',
            'comment' => 'Style of learning that this activity involves. ' .
                'Active_aided implies a combo, passive_to_active implies passive prompt with active response',
            'precision' => null,
            'fixed' => null
        ],
        'exclude_words' => [
            'type' => 'string',
            'length' => null,
            'null' => false,
            'default' => 'b\'0\'',
            'collate' => null,
            'comment' => 'Whether the activity type for word cards should be ' .
                'excluded from the algorithm that uses it',
            'precision' => null,
            'fixed' => null
        ],
        'exclude_patterns' => [
            'type' => 'string',
            'length' => null,
            'null' => false,
            'default' => 'b\'0\'',
            'collate' => null,
            'comment' => 'Whether the activity type for pattern cards should ' .
                'be excluded from the algorithm that uses it',
            'precision' => null,
            'fixed' => null
        ],
        '_constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => [
                    'id'
                ],
                'length' => []
            ],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'type' => 1,
                'specific_skill' => 'reading',
                'global_skill' => 'reading',
                'prompt_response_pairs_words' => 'l-i,l-e',
                'prompt_response_pairs_patterns' => 'l-e',
                'exercise_type_words' => 'match-the-pair',
                'exercise_type_patterns' => 'match-the-pair',
                'learning_percentage_words' => 100,
                'learning_percentage_patterns' => 100,
                'review_percentage_words' => 25,
                'review_percentage_patterns' => 50,
                'learning_style' => 'passive',
                'exclude_words' => '0',
                'exclude_patterns' => '0'
            ],
            [
                'id' => 2,
                'type' => 1,
                'specific_skill' => 'reading',
                'global_skill' => 'reading',
                'prompt_response_pairs_words' => 'l-i,l-e',
                'prompt_response_pairs_patterns' => 'l-i,l-e',
                'exercise_type_words' => 'multiple-choice',
                'exercise_type_patterns' => 'multiple-choice',
                'learning_percentage_words' => 100,
                'learning_percentage_patterns' => 100,
                'review_percentage_words' => 25,
                'review_percentage_patterns' => 50,
                'learning_style' => 'passive',
                'exclude_words' => '0',
                'exclude_patterns' => '0'
            ],
            [
                'id' => 3,
                'type' => 2,
                'specific_skill' => 'reading',
                'global_skill' => 'reading',
                'prompt_response_pairs_words' => 'i-l,e-l',
                'prompt_response_pairs_patterns' => '',
                'exercise_type_words' => 'match-the-pair',
                'exercise_type_patterns' => '',
                'learning_percentage_words' => 100,
                'learning_percentage_patterns' => 100,
                'review_percentage_words' => 25,
                'review_percentage_patterns' => 0,
                'learning_style' => 'passive',
                'exclude_words' => '0',
                'exclude_patterns' => '1'
            ],
            [
                'id' => 4,
                'type' => 2,
                'specific_skill' => 'reading',
                'global_skill' => 'reading',
                'prompt_response_pairs_words' => 'i-l,e-l',
                'prompt_response_pairs_patterns' => 'i-l,e-l',
                'exercise_type_words' => 'multiple-choice',
                'exercise_type_patterns' => 'multiple-choice',
                'learning_percentage_words' => 100,
                'learning_percentage_patterns' => 100,
                'review_percentage_words' => 25,
                'review_percentage_patterns' => 50,
                'learning_style' => 'passive',
                'exclude_words' => '0',
                'exclude_patterns' => '0'
            ],
            [
                'id' => 5,
                'type' => 3,
                'specific_skill' => 'listening (comprehension)',
                'global_skill' => 'listening',
                'prompt_response_pairs_words' => 'a-e',
                'prompt_response_pairs_patterns' => '',
                'exercise_type_words' => 'match-the-pair',
                'exercise_type_patterns' => '',
                'learning_percentage_words' => 50,
                'learning_percentage_patterns' => 50,
                'review_percentage_words' => 50,
                'review_percentage_patterns' => 0,
                'learning_style' => 'passive',
                'exclude_words' => '0',
                'exclude_patterns' => '1'
            ],
            [
                'id' => 6,
                'type' => 3,
                'specific_skill' => 'listening (comprehension)',
                'global_skill' => 'listening',
                'prompt_response_pairs_words' => 'a-e',
                'prompt_response_pairs_patterns' => 'a-e',
                'exercise_type_words' => 'multiple-choice',
                'exercise_type_patterns' => 'multiple-choice',
                'learning_percentage_words' => 50,
                'learning_percentage_patterns' => 50,
                'review_percentage_words' => 50,
                'review_percentage_patterns' => 20,
                'learning_style' => 'passive',
                'exclude_words' => '0',
                'exclude_patterns' => '0'
            ],
            [
                'id' => 7,
                'type' => 4,
                'specific_skill' => 'listening (phonemic awareness)',
                'global_skill' => 'listening',
                'prompt_response_pairs_words' => 'a-l',
                'prompt_response_pairs_patterns' => '',
                'exercise_type_words' => 'match-the-pair',
                'exercise_type_patterns' => '',
                'learning_percentage_words' => 25,
                'learning_percentage_patterns' => 25,
                'review_percentage_words' => 15,
                'review_percentage_patterns' => 0,
                'learning_style' => 'passive',
                'exclude_words' => '0',
                'exclude_patterns' => '1'
            ],
            [
                'id' => 8,
                'type' => 4,
                'specific_skill' => 'listening (phonemic awareness)',
                'global_skill' => 'listening',
                'prompt_response_pairs_words' => 'a-l',
                'prompt_response_pairs_patterns' => 'a-l',
                'exercise_type_words' => 'multiple-choice',
                'exercise_type_patterns' => 'multiple-choice',
                'learning_percentage_words' => 25,
                'learning_percentage_patterns' => 25,
                'review_percentage_words' => 15,
                'review_percentage_patterns' => 15,
                'learning_style' => 'passive',
                'exclude_words' => '0',
                'exclude_patterns' => '0'
            ],
            [
                'id' => 9,
                'type' => 5,
                'specific_skill' => 'phonemic',
                'global_skill' => 'listening',
                'prompt_response_pairs_words' => 'a-l',
                'prompt_response_pairs_patterns' => 'a-l',
                'exercise_type_words' => 'fill_in_the_blanks_typing',
                'exercise_type_patterns' => 'Word Fill (Multiple Choice)',
                'learning_percentage_words' => 2,
                'learning_percentage_patterns' => 0,
                'review_percentage_words' => 2,
                'review_percentage_patterns' => 2,
                'learning_style' => 'passive_to_active',
                'exclude_words' => '0',
                'exclude_patterns' => '1'
            ],
            [
                'id' => 10,
                'type' => 5,
                'specific_skill' => 'phonemic',
                'global_skill' => 'listening',
                'prompt_response_pairs_words' => 'a-l',
                'prompt_response_pairs_patterns' => 'a-l',
                'exercise_type_words' => 'fill_in_the_blanks_mcq',
                'exercise_type_patterns' => 'Word Fill (Type)',
                'learning_percentage_words' => 2,
                'learning_percentage_patterns' => 0,
                'review_percentage_words' => 2,
                'review_percentage_patterns' => 2,
                'learning_style' => 'passive_to_active',
                'exclude_words' => '1',
                'exclude_patterns' => '1'
            ],
            [
                'id' => 11,
                'type' => 6,
                'specific_skill' => 'phonemic',
                'global_skill' => 'listening',
                'prompt_response_pairs_words' => 'a-l',
                'prompt_response_pairs_patterns' => 'a-l',
                'exercise_type_words' => 'anagram',
                'exercise_type_patterns' => 'Building Blocks',
                'learning_percentage_words' => 25,
                'learning_percentage_patterns' => 25,
                'review_percentage_words' => 15,
                'review_percentage_patterns' => 15,
                'learning_style' => 'passive_to_active',
                'exclude_words' => '0',
                'exclude_patterns' => '1'
            ],
            [
                'id' => 12,
                'type' => 7,
                'specific_skill' => 'listening / spelling',
                'global_skill' => 'listening',
                'prompt_response_pairs_words' => 'a-l',
                'prompt_response_pairs_patterns' => 'a-l',
                'exercise_type_words' => 'Type Answer',
                'exercise_type_patterns' => 'Type Answer',
                'learning_percentage_words' => 1,
                'learning_percentage_patterns' => 0,
                'review_percentage_words' => 1,
                'review_percentage_patterns' => 0,
                'learning_style' => 'passive',
                'exclude_words' => '1',
                'exclude_patterns' => '1'
            ],
            [
                'id' => 13,
                'type' => 8,
                'specific_skill' => 'spelling',
                'global_skill' => 'writing',
                'prompt_response_pairs_words' => 'e-l',
                'prompt_response_pairs_patterns' => 'e-l',
                'exercise_type_words' => 'anagram',
                'exercise_type_patterns' => 'Building Blocks',
                'learning_percentage_words' => 30,
                'learning_percentage_patterns' => 30,
                'review_percentage_words' => 30,
                'review_percentage_patterns' => 30,
                'learning_style' => 'active_aided',
                'exclude_words' => '0',
                'exclude_patterns' => '1'
            ],
            [
                'id' => 14,
                'type' => 9,
                'specific_skill' => 'spelling',
                'global_skill' => 'writing',
                'prompt_response_pairs_words' => 'e-l',
                'prompt_response_pairs_patterns' => 'e-l',
                'exercise_type_words' => 'Type Answer',
                'exercise_type_patterns' => 'Type Answer',
                'learning_percentage_words' => 5,
                'learning_percentage_patterns' => 0,
                'review_percentage_words' => 5,
                'review_percentage_patterns' => 0,
                'learning_style' => 'active',
                'exclude_words' => '1',
                'exclude_patterns' => '1'
            ],
        ];
        parent::init();
    }
}
