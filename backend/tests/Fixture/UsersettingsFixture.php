<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersettingsFixture
 *
 */
class UsersettingsFixture extends TestFixture
{
    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 12, 'unsigned' => true, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'user_id' => ['type' => 'integer', 'length' => 12, 'unsigned' => true, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'display_name' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'profile_picture' => ['type' => 'string', 'length' => 256, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'aws_profile_link' => ['type' => 'string', 'length' => 500, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'location' => ['type' => 'string', 'length' => 500, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'profile_desc' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null],
        'push_notification' => ['type' => 'string', 'length' => null, 'null' => false, 'default' => '0', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'email_notification' => ['type' => 'string', 'length' => null, 'null' => false, 'default' => '0', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'news_event' => ['type' => 'string', 'length' => null, 'null' => false, 'default' => '0', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'motivation' => ['type' => 'string', 'length' => null, 'null' => false, 'default' => '0', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'motivation_time' => ['type' => 'time', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'age_over_adult' => ['type' => 'string', 'length' => null, 'null' => false, 'default' => '0', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'parental_lock' => ['type' => 'string', 'length' => 256, 'null' => true, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'parental_lock_on' => ['type' => 'string', 'length' => null, 'null' => false, 'default' => '0', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'badges' => ['type' => 'string', 'length' => 100, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'public_profile' => ['type' => 'string', 'length' => null, 'null' => false, 'default' => '0', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'public_leaderboard' => ['type' => 'string', 'length' => null, 'null' => false, 'default' => '0', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'audio_archive' => ['type' => 'string', 'length' => null, 'null' => false, 'default' => '0', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'hearing' => ['type' => 'string', 'length' => null, 'null' => false, 'default' => '0', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'lastupdated' => ['type' => 'timestamp', 'length' => null, 'null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => '', 'precision' => null],
        '_indexes' => [
            'user_id' => ['type' => 'index', 'columns' => ['user_id'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
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
                'user_id' => 1,
                'display_name' => 'Lorem ipsum dolor sit amet',
                'profile_picture' => 'Lorem ipsum dolor sit amet',
                'aws_profile_link' => 'Lorem ipsum dolor sit amet',
                'location' => 'Lorem ipsum dolor sit amet',
                'profile_desc' => 'Lorem ipsum dolor sit amet.',
                'push_notification' => 'Lorem ipsum dolor sit amet',
                'email_notification' => 'Lorem ipsum dolor sit amet',
                'news_event' => 'Lorem ipsum dolor sit amet',
                'motivation' => 'Lorem ipsum dolor sit amet',
                'motivation_time' => '18:07:14',
                'age_over_adult' => 'Lorem ipsum dolor sit amet',
                'parental_lock' => 'Lorem ipsum dolor sit amet',
                'parental_lock_on' => 'Lorem ipsum dolor sit amet',
                'badges' => 'Lorem ipsum dolor sit amet',
                'public_profile' => 'Lorem ipsum dolor sit amet',
                'public_leaderboard' => 'Lorem ipsum dolor sit amet',
                'audio_archive' => 'Lorem ipsum dolor sit amet',
                'hearing' => 'Lorem ipsum dolor sit amet',
                'lastupdated' => 1635358034
            ],
        ];
        parent::init();
    }
}
