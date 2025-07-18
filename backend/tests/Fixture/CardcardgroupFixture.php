<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CardcardgroupFixture
 *
 */
class CardcardgroupFixture extends TestFixture
{
    /* Rename table to use the custom name in the actual table since the naming
       is all screwed up and that causes the table to be named incorrectly and
       therefore not found in the actual controller. */
    public $table = 'card_card_groups';

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
            'unsigned' => true,
            'null' => false,
            'default' => null,
            'comment' => '',
            'autoIncrement' => true,
            'precision' => null
        ],
        'card_id' => [
            'type' => 'integer',
            'length' => 11,
            'unsigned' => true,
            'null' => false,
            'default' => null,
            'comment' => '',
            'precision' => null,
            'autoIncrement' => null
        ],
        'card_group_id' => [
            'type' => 'integer',
            'length' => 11,
            'unsigned' => true,
            'null' => false,
            'default' => null,
            'comment' => '',
            'precision' => null,
            'autoIncrement' => null
        ],
        'created' => [
            'type' => 'datetime',
            'length' => null,
            'null' => true,
            'default' => null,
            'comment' => '',
            'precision' => null
        ],
        'modified' => [
            'type' => 'datetime',
            'length' => null,
            'null' => true,
            'default' => null,
            'comment' => '',
            'precision' => null
        ],
        '_indexes' => [
            'card_id' => [
                'type' => 'index',
                'columns' => ['card_id'],
                'length' => []
            ],
            'card_group_id' => [
                'type' => 'index',
                'columns' => ['card_group_id'],
                'length' => []
            ],
        ],
        '_constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
                'length' => []
            ],
            'FK_card_card_groups_card_groups' => [
                'type' => 'foreign',
                'columns' => ['card_group_id'],
                'references' => [
                    'card_groups',
                    'id'
                ],
                'update' => 'noAction',
                'delete' => 'cascade',
                'length' => []
            ],
            'FK_card_card_groups_cards' => [
                'type' => 'foreign',
                'columns' => ['card_id'],
                'references' => [
                    'cards',
                    'id'
                ],
                'update' => 'noAction',
                'delete' => 'cascade',
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
                'card_id' => 1,
                'card_group_id' => 1,
                'created' => '2018-05-23 16:05:36',
                'modified' => '2018-05-23 16:05:36'
            ],
            [
                'id' => 2,
                'card_id' => 1,
                'card_group_id' => 2,
                'created' => '2018-07-17 14:31:53',
                'modified' => '2018-07-17 14:31:53'
            ],
            [
                'id' => 3,
                'card_id' => 2,
                'card_group_id' => 2,
                'created' => '2018-07-17 14:31:53',
                'modified' => '2018-07-17 14:31:53'
            ],
            [
                'id' => 4,
                'card_id' => 3,
                'card_group_id' => 2,
                'created' => '2018-07-17 14:31:53',
                'modified' => '2018-07-17 14:31:53'
            ],
            [
                'id' => 5,
                'card_id' => 4,
                'card_group_id' => 2,
                'created' => '2018-07-17 14:31:53',
                'modified' => '2018-07-17 14:31:53'
            ],
            [
                'id' => 6,
                'card_id' => 5,
                'card_group_id' => 2,
                'created' => '2018-07-17 14:31:53',
                'modified' => '2018-07-17 14:31:53'
            ],
            [
                'id' => 7,
                'card_id' => 6,
                'card_group_id' => 2,
                'created' => '2018-07-17 14:31:53',
                'modified' => '2018-07-17 14:31:53'
            ],
            [
                'id' => 8,
                'card_id' => 7,
                'card_group_id' => 2,
                'created' => '2018-07-17 14:31:53',
                'modified' => '2018-07-17 14:31:53'
            ],
            [
                'id' => 9,
                'card_id' => 8,
                'card_group_id' => 2,
                'created' => '2018-07-17 14:31:53',
                'modified' => '2018-07-17 14:31:53'
            ],
            [
                'id' => 10,
                'card_id' => 9,
                'card_group_id' => 2,
                'created' => '2018-07-17 14:31:53',
                'modified' => '2018-07-17 14:31:53'
            ],
            [
                'id' => 11,
                'card_id' => 10,
                'card_group_id' => 2,
                'created' => '2018-07-17 14:31:53',
                'modified' => '2018-07-17 14:31:53'
            ],
            [
                'id' => 12,
                'card_id' => 233,
                'card_group_id' => 3,
                'created' => '2018-07-18 14:26:47',
                'modified' => '2018-07-18 14:26:47'
            ],
            [
                'id' => 13,
                'card_id' => 628,
                'card_group_id' => 3,
                'created' => '2018-07-18 14:26:47',
                'modified' => '2018-07-18 14:26:47'
            ],
            [
                'id' => 14,
                'card_id' => 554,
                'card_group_id' => 3,
                'created' => '2018-07-18 14:26:47',
                'modified' => '2018-07-18 14:26:47'
            ],
            [
                'id' => 15,
                'card_id' => 630,
                'card_group_id' => 3,
                'created' => '2018-07-18 14:26:47',
                'modified' => '2018-07-18 14:26:47'
            ],
            [
                'id' => 16,
                'card_id' => 558,
                'card_group_id' => 3,
                'created' => '2018-07-18 14:26:47',
                'modified' => '2018-07-18 14:26:47'
            ],
            [
                'id' => 17,
                'card_id' => 631,
                'card_group_id' => 3,
                'created' => '2018-07-18 14:26:47',
                'modified' => '2018-07-18 14:26:47'
            ],
            [
                'id' => 18,
                'card_id' => 549,
                'card_group_id' => 3,
                'created' => '2018-07-18 14:26:47',
                'modified' => '2018-07-18 14:26:47'
            ],
            [
                'id' => 19,
                'card_id' => 634,
                'card_group_id' => 3,
                'created' => '2018-07-18 14:26:47',
                'modified' => '2018-07-18 14:26:47'
            ],
            [
                'id' => 20,
                'card_id' => 635,
                'card_group_id' => 3,
                'created' => '2018-07-18 14:26:47',
                'modified' => '2018-07-18 14:26:47'
            ],
            [
                'id' => 21,
                'card_id' => 636,
                'card_group_id' => 3,
                'created' => '2018-07-18 14:26:47',
                'modified' => '2018-07-18 14:26:47'
            ],
            [
                'id' => 22,
                'card_id' => 637,
                'card_group_id' => 3,
                'created' => '2018-07-18 14:26:47',
                'modified' => '2018-07-18 14:26:47'
            ],
            [
                'id' => 23,
                'card_id' => 1,
                'card_group_id' => 4,
                'created' => '2018-08-10 12:25:43',
                'modified' => '2018-08-10 12:25:43'
            ],
            [
                'id' => 24,
                'card_id' => 2,
                'card_group_id' => 4,
                'created' => '2018-08-10 12:25:43',
                'modified' => '2018-08-10 12:25:43'
            ],
            [
                'id' => 25,
                'card_id' => 3,
                'card_group_id' => 4,
                'created' => '2018-08-10 12:25:43',
                'modified' => '2018-08-10 12:25:43'
            ],
            [
                'id' => 26,
                'card_id' => 4,
                'card_group_id' => 4,
                'created' => '2018-08-10 12:25:43',
                'modified' => '2018-08-10 12:25:43'
            ],
            [
                'id' => 27,
                'card_id' => 5,
                'card_group_id' => 4,
                'created' => '2018-08-10 12:25:43',
                'modified' => '2018-08-10 12:25:43'
            ],
            [
                'id' => 28,
                'card_id' => 6,
                'card_group_id' => 4,
                'created' => '2018-08-10 12:25:43',
                'modified' => '2018-08-10 12:25:43'
            ],
            [
                'id' => 29,
                'card_id' => 7,
                'card_group_id' => 4,
                'created' => '2018-08-10 12:25:43',
                'modified' => '2018-08-10 12:25:43'
            ],
            [
                'id' => 30,
                'card_id' => 8,
                'card_group_id' => 4,
                'created' => '2018-08-10 12:25:43',
                'modified' => '2018-08-10 12:25:43'
            ],
            [
                'id' => 31,
                'card_id' => 9,
                'card_group_id' => 4,
                'created' => '2018-08-10 12:25:43',
                'modified' => '2018-08-10 12:25:43'
            ],
            [
                'id' => 32,
                'card_id' => 10,
                'card_group_id' => 4,
                'created' => '2018-08-10 12:25:43',
                'modified' => '2018-08-10 12:25:43'
            ],
            [
                'id' => 33,
                'card_id' => 1,
                'card_group_id' => 5,
                'created' => '2018-08-10 12:26:51',
                'modified' => '2018-08-10 12:26:51'
            ],
            [
                'id' => 34,
                'card_id' => 2,
                'card_group_id' => 5,
                'created' => '2018-08-10 12:26:51',
                'modified' => '2018-08-10 12:26:51'
            ],
            [
                'id' => 35,
                'card_id' => 3,
                'card_group_id' => 5,
                'created' => '2018-08-10 12:26:51',
                'modified' => '2018-08-10 12:26:51'
            ],
            [
                'id' => 36,
                'card_id' => 4,
                'card_group_id' => 5,
                'created' => '2018-08-10 12:26:51',
                'modified' => '2018-08-10 12:26:51'
            ],
            [
                'id' => 37,
                'card_id' => 5,
                'card_group_id' => 5,
                'created' => '2018-08-10 12:26:51',
                'modified' => '2018-08-10 12:26:51'
            ],
            [
                'id' => 38,
                'card_id' => 6,
                'card_group_id' => 6,
                'created' => '2018-08-10 12:27:28',
                'modified' => '2018-08-10 12:27:28'
            ],
            [
                'id' => 39,
                'card_id' => 7,
                'card_group_id' => 6,
                'created' => '2018-08-10 12:27:28',
                'modified' => '2018-08-10 12:27:28'
            ],
            [
                'id' => 40,
                'card_id' => 8,
                'card_group_id' => 6,
                'created' => '2018-08-10 12:27:28',
                'modified' => '2018-08-10 12:27:28'
            ],
            [
                'id' => 41,
                'card_id' => 9,
                'card_group_id' => 6,
                'created' => '2018-08-10 12:27:28',
                'modified' => '2018-08-10 12:27:28'
            ],
            [
                'id' => 42,
                'card_id' => 10,
                'card_group_id' => 6,
                'created' => '2018-08-10 12:27:28',
                'modified' => '2018-08-10 12:27:28'
            ],
            [
                'id' => 43,
                'card_id' => 11,
                'card_group_id' => 7,
                'created' => '2018-08-23 21:53:23',
                'modified' => '2018-08-23 21:53:23'
            ],
            [
                'id' => 44,
                'card_id' => 13,
                'card_group_id' => 7,
                'created' => '2018-08-23 21:53:23',
                'modified' => '2018-08-23 21:53:23'
            ],
            [
                'id' => 45,
                'card_id' => 15,
                'card_group_id' => 7,
                'created' => '2018-08-23 21:53:23',
                'modified' => '2018-08-23 21:53:23'
            ],
            [
                'id' => 46,
                'card_id' => 17,
                'card_group_id' => 7,
                'created' => '2018-08-23 21:53:23',
                'modified' => '2018-08-23 21:53:23'
            ],
            [
                'id' => 47,
                'card_id' => 19,
                'card_group_id' => 7,
                'created' => '2018-08-23 21:53:23',
                'modified' => '2018-08-23 21:53:23'
            ],
            [
                'id' => 48,
                'card_id' => 21,
                'card_group_id' => 7,
                'created' => '2018-08-23 21:53:23',
                'modified' => '2018-08-23 21:53:23'
            ],
            [
                'id' => 49,
                'card_id' => 23,
                'card_group_id' => 7,
                'created' => '2018-08-23 21:53:23',
                'modified' => '2018-08-23 21:53:23'
            ],
            [
                'id' => 50,
                'card_id' => 26,
                'card_group_id' => 7,
                'created' => '2018-08-23 21:53:23',
                'modified' => '2018-08-23 21:53:23'
            ],
            [
                'id' => 51,
                'card_id' => 28,
                'card_group_id' => 7,
                'created' => '2018-08-23 21:53:23',
                'modified' => '2018-08-23 21:53:23'
            ],
            [
                'id' => 52,
                'card_id' => 30,
                'card_group_id' => 7,
                'created' => '2018-08-23 21:53:23',
                'modified' => '2018-08-23 21:53:23'
            ],
            [
                'id' => 53,
                'card_id' => 33,
                'card_group_id' => 8,
                'created' => '2018-09-05 13:05:51',
                'modified' => '2018-09-05 13:05:51'
            ],
            [
                'id' => 54,
                'card_id' => 35,
                'card_group_id' => 8,
                'created' => '2018-09-05 13:05:51',
                'modified' => '2018-09-05 13:05:51'
            ],
            [
                'id' => 55,
                'card_id' => 37,
                'card_group_id' => 8,
                'created' => '2018-09-05 13:05:51',
                'modified' => '2018-09-05 13:05:51'
            ],
            [
                'id' => 56,
                'card_id' => 39,
                'card_group_id' => 8,
                'created' => '2018-09-05 13:05:51',
                'modified' => '2018-09-05 13:05:51'
            ],
            [
                'id' => 57,
                'card_id' => 41,
                'card_group_id' => 8,
                'created' => '2018-09-05 13:05:51',
                'modified' => '2018-09-05 13:05:51'
            ],
            [
                'id' => 58,
                'card_id' => 43,
                'card_group_id' => 8,
                'created' => '2018-09-05 13:05:51',
                'modified' => '2018-09-05 13:05:51'
            ],
            [
                'id' => 59,
                'card_id' => 45,
                'card_group_id' => 8,
                'created' => '2018-09-05 13:05:51',
                'modified' => '2018-09-05 13:05:51'
            ],
            [
                'id' => 60,
                'card_id' => 47,
                'card_group_id' => 8,
                'created' => '2018-09-05 13:05:51',
                'modified' => '2018-09-05 13:05:51'
            ],
            [
                'id' => 61,
                'card_id' => 12,
                'card_group_id' => 9,
                'created' => '2018-09-05 13:28:34',
                'modified' => '2018-09-05 13:28:34'
            ],
            [
                'id' => 62,
                'card_id' => 14,
                'card_group_id' => 9,
                'created' => '2018-09-05 13:28:34',
                'modified' => '2018-09-05 13:28:34'
            ],
            [
                'id' => 63,
                'card_id' => 16,
                'card_group_id' => 9,
                'created' => '2018-09-05 13:28:34',
                'modified' => '2018-09-05 13:28:34'
            ],
            [
                'id' => 64,
                'card_id' => 18,
                'card_group_id' => 9,
                'created' => '2018-09-05 13:28:34',
                'modified' => '2018-09-05 13:28:34'
            ],
            [
                'id' => 65,
                'card_id' => 20,
                'card_group_id' => 9,
                'created' => '2018-09-05 13:28:34',
                'modified' => '2018-09-05 13:28:34'
            ],
            [
                'id' => 66,
                'card_id' => 22,
                'card_group_id' => 9,
                'created' => '2018-09-05 13:28:34',
                'modified' => '2018-09-05 13:28:34'
            ],
            [
                'id' => 67,
                'card_id' => 24,
                'card_group_id' => 9,
                'created' => '2018-09-05 13:28:34',
                'modified' => '2018-09-05 13:28:34'
            ],
            [
                'id' => 68,
                'card_id' => 34,
                'card_group_id' => 10,
                'created' => '2018-09-05 13:38:53',
                'modified' => '2018-09-05 13:38:53'
            ],
            [
                'id' => 69,
                'card_id' => 36,
                'card_group_id' => 10,
                'created' => '2018-09-05 13:38:53',
                'modified' => '2018-09-05 13:38:53'
            ],
            [
                'id' => 70,
                'card_id' => 38,
                'card_group_id' => 10,
                'created' => '2018-09-05 13:38:53',
                'modified' => '2018-09-05 13:38:53'
            ],
            [
                'id' => 71,
                'card_id' => 40,
                'card_group_id' => 10,
                'created' => '2018-09-05 13:38:53',
                'modified' => '2018-09-05 13:38:53'
            ],
            [
                'id' => 72,
                'card_id' => 42,
                'card_group_id' => 10,
                'created' => '2018-09-05 13:38:53',
                'modified' => '2018-09-05 13:38:53'
            ],
            [
                'id' => 73,
                'card_id' => 44,
                'card_group_id' => 10,
                'created' => '2018-09-05 13:38:53',
                'modified' => '2018-09-05 13:38:53'
            ],
            [
                'id' => 74,
                'card_id' => 46,
                'card_group_id' => 10,
                'created' => '2018-09-05 13:38:53',
                'modified' => '2018-09-05 13:38:53'
            ],
            [
                'id' => 75,
                'card_id' => 48,
                'card_group_id' => 10,
                'created' => '2018-09-05 13:38:53',
                'modified' => '2018-09-05 13:38:53'
            ],
            [
                'id' => 76,
                'card_id' => 97,
                'card_group_id' => 11,
                'created' => '2018-09-06 07:44:25',
                'modified' => '2018-09-06 07:44:25'
            ],
            [
                'id' => 77,
                'card_id' => 99,
                'card_group_id' => 11,
                'created' => '2018-09-06 07:44:25',
                'modified' => '2018-09-06 07:44:25'
            ],
            [
                'id' => 78,
                'card_id' => 101,
                'card_group_id' => 11,
                'created' => '2018-09-06 07:44:25',
                'modified' => '2018-09-06 07:44:25'
            ],
            [
                'id' => 79,
                'card_id' => 103,
                'card_group_id' => 11,
                'created' => '2018-09-06 07:44:25',
                'modified' => '2018-09-06 07:44:25'
            ],
            [
                'id' => 80,
                'card_id' => 105,
                'card_group_id' => 11,
                'created' => '2018-09-06 07:44:25',
                'modified' => '2018-09-06 07:44:25'
            ],
            [
                'id' => 81,
                'card_id' => 107,
                'card_group_id' => 11,
                'created' => '2018-09-06 07:44:25',
                'modified' => '2018-09-06 07:44:25'
            ],
            [
                'id' => 82,
                'card_id' => 109,
                'card_group_id' => 11,
                'created' => '2018-09-06 07:44:25',
                'modified' => '2018-09-06 07:44:25'
            ],
            [
                'id' => 83,
                'card_id' => 111,
                'card_group_id' => 11,
                'created' => '2018-09-06 07:44:25',
                'modified' => '2018-09-06 07:44:25'
            ],
            [
                'id' => 84,
                'card_id' => 113,
                'card_group_id' => 11,
                'created' => '2018-09-06 07:44:25',
                'modified' => '2018-09-06 07:44:25'
            ],
            [
                'id' => 85,
                'card_id' => 115,
                'card_group_id' => 11,
                'created' => '2018-09-06 07:44:25',
                'modified' => '2018-09-06 07:44:25'
            ],
            [
                'id' => 86,
                'card_id' => 117,
                'card_group_id' => 11,
                'created' => '2018-09-06 07:44:25',
                'modified' => '2018-09-06 07:44:25'
            ],
            [
                'id' => 87,
                'card_id' => 119,
                'card_group_id' => 11,
                'created' => '2018-09-06 07:44:25',
                'modified' => '2018-09-06 07:44:25'
            ],
            [
                'id' => 88,
                'card_id' => 121,
                'card_group_id' => 11,
                'created' => '2018-09-06 07:44:25',
                'modified' => '2018-09-06 07:44:25'
            ],
            [
                'id' => 89,
                'card_id' => 123,
                'card_group_id' => 11,
                'created' => '2018-09-06 07:44:25',
                'modified' => '2018-09-06 07:44:25'
            ],
            [
                'id' => 90,
                'card_id' => 98,
                'card_group_id' => 12,
                'created' => '2018-09-06 07:55:46',
                'modified' => '2018-09-06 07:55:46'
            ],
            [
                'id' => 91,
                'card_id' => 100,
                'card_group_id' => 12,
                'created' => '2018-09-06 07:55:46',
                'modified' => '2018-09-06 07:55:46'
            ],
            [
                'id' => 92,
                'card_id' => 102,
                'card_group_id' => 12,
                'created' => '2018-09-06 07:55:46',
                'modified' => '2018-09-06 07:55:46'
            ],
            [
                'id' => 93,
                'card_id' => 104,
                'card_group_id' => 12,
                'created' => '2018-09-06 07:55:46',
                'modified' => '2018-09-06 07:55:46'
            ],
            [
                'id' => 94,
                'card_id' => 106,
                'card_group_id' => 12,
                'created' => '2018-09-06 07:55:46',
                'modified' => '2018-09-06 07:55:46'
            ],
            [
                'id' => 95,
                'card_id' => 108,
                'card_group_id' => 12,
                'created' => '2018-09-06 07:55:46',
                'modified' => '2018-09-06 07:55:46'
            ],
            [
                'id' => 96,
                'card_id' => 40,
                'card_group_id' => 12,
                'created' => '2018-09-06 07:55:46',
                'modified' => '2018-09-06 07:55:46'
            ],
            [
                'id' => 97,
                'card_id' => 112,
                'card_group_id' => 12,
                'created' => '2018-09-06 07:55:46',
                'modified' => '2018-09-06 07:55:46'
            ],
            [
                'id' => 98,
                'card_id' => 114,
                'card_group_id' => 12,
                'created' => '2018-09-06 07:55:46',
                'modified' => '2018-09-06 07:55:46'
            ],
            [
                'id' => 99,
                'card_id' => 116,
                'card_group_id' => 12,
                'created' => '2018-09-06 07:55:46',
                'modified' => '2018-09-06 07:55:46'
            ],
            [
                'id' => 100,
                'card_id' => 118,
                'card_group_id' => 12,
                'created' => '2018-09-06 07:55:46',
                'modified' => '2018-09-06 07:55:46'
            ],
            [
                'id' => 101,
                'card_id' => 120,
                'card_group_id' => 12,
                'created' => '2018-09-06 07:55:46',
                'modified' => '2018-09-06 07:55:46'
            ],
            [
                'id' => 102,
                'card_id' => 122,
                'card_group_id' => 12,
                'created' => '2018-09-06 07:55:46',
                'modified' => '2018-09-06 07:55:46'
            ],
            [
                'id' => 103,
                'card_id' => 124,
                'card_group_id' => 12,
                'created' => '2018-09-06 07:55:46',
                'modified' => '2018-09-06 07:55:46'
            ],
            [
                'id' => 104,
                'card_id' => 8,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:52',
                'modified' => '2018-09-10 05:22:52'
            ],
            [
                'id' => 105,
                'card_id' => 784,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:52',
                'modified' => '2018-09-10 05:22:52'
            ],
            [
                'id' => 106,
                'card_id' => 5,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:52',
                'modified' => '2018-09-10 05:22:52'
            ],
            [
                'id' => 107,
                'card_id' => 786,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:52',
                'modified' => '2018-09-10 05:22:52'
            ],
            [
                'id' => 108,
                'card_id' => 7,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:52',
                'modified' => '2018-09-10 05:22:52'
            ],
            [
                'id' => 109,
                'card_id' => 788,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:52',
                'modified' => '2018-09-10 05:22:52'
            ],
            [
                'id' => 110,
                'card_id' => 3,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:52',
                'modified' => '2018-09-10 05:22:52'
            ],
            [
                'id' => 111,
                'card_id' => 790,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 112,
                'card_id' => 791,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 113,
                'card_id' => 792,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 114,
                'card_id' => 9,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 115,
                'card_id' => 794,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 116,
                'card_id' => 168,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 117,
                'card_id' => 796,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 118,
                'card_id' => 797,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 119,
                'card_id' => 798,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 120,
                'card_id' => 775,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 121,
                'card_id' => 800,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 122,
                'card_id' => 66,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 123,
                'card_id' => 802,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 124,
                'card_id' => 60,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 125,
                'card_id' => 804,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 126,
                'card_id' => 56,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 127,
                'card_id' => 806,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 128,
                'card_id' => 65,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 129,
                'card_id' => 808,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 130,
                'card_id' => 52,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 131,
                'card_id' => 810,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 132,
                'card_id' => 279,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 133,
                'card_id' => 812,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 134,
                'card_id' => 813,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 135,
                'card_id' => 814,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 136,
                'card_id' => 815,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 137,
                'card_id' => 816,
                'card_group_id' => 13,
                'created' => '2018-09-10 05:22:53',
                'modified' => '2018-09-10 05:22:53'
            ],
            [
                'id' => 138,
                'card_id' => 4089,
                'card_group_id' => 14,
                'created' => '2018-09-18 08:58:03',
                'modified' => '2018-09-18 08:58:03'
            ],
            [
                'id' => 139,
                'card_id' => 4088,
                'card_group_id' => 14,
                'created' => '2018-09-18 08:58:03',
                'modified' => '2018-09-18 08:58:03'
            ],
            [
                'id' => 140,
                'card_id' => 4090,
                'card_group_id' => 14,
                'created' => '2018-09-18 08:58:03',
                'modified' => '2018-09-18 08:58:03'
            ],
            [
                'id' => 141,
                'card_id' => 4091,
                'card_group_id' => 14,
                'created' => '2018-09-18 08:58:03',
                'modified' => '2018-09-18 08:58:03'
            ],
            [
                'id' => 142,
                'card_id' => 4092,
                'card_group_id' => 14,
                'created' => '2018-09-18 08:58:03',
                'modified' => '2018-09-18 08:58:03'
            ],
            [
                'id' => 143,
                'card_id' => 4093,
                'card_group_id' => 14,
                'created' => '2018-09-18 08:58:03',
                'modified' => '2018-09-18 08:58:03'
            ],
            [
                'id' => 144,
                'card_id' => 4094,
                'card_group_id' => 14,
                'created' => '2018-09-18 08:58:04',
                'modified' => '2018-09-18 08:58:04'
            ],
            [
                'id' => 145,
                'card_id' => 4095,
                'card_group_id' => 14,
                'created' => '2018-09-18 08:58:04',
                'modified' => '2018-09-18 08:58:04'
            ],
            [
                'id' => 146,
                'card_id' => 999,
                'card_group_id' => 15,
                'created' => '2018-09-18 09:20:02',
                'modified' => '2018-09-18 09:20:02'
            ],
            [
                'id' => 147,
                'card_id' => 1000,
                'card_group_id' => 15,
                'created' => '2018-09-18 09:20:02',
                'modified' => '2018-09-18 09:20:02'
            ],
            [
                'id' => 148,
                'card_id' => 1001,
                'card_group_id' => 15,
                'created' => '2018-09-18 09:20:02',
                'modified' => '2018-09-18 09:20:02'
            ],
            [
                'id' => 149,
                'card_id' => 1002,
                'card_group_id' => 15,
                'created' => '2018-09-18 09:20:02',
                'modified' => '2018-09-18 09:20:02'
            ],
            [
                'id' => 150,
                'card_id' => 1003,
                'card_group_id' => 15,
                'created' => '2018-09-18 09:20:02',
                'modified' => '2018-09-18 09:20:02'
            ],
            [
                'id' => 151,
                'card_id' => 1004,
                'card_group_id' => 15,
                'created' => '2018-09-18 09:20:02',
                'modified' => '2018-09-18 09:20:02'
            ],
            [
                'id' => 152,
                'card_id' => 1005,
                'card_group_id' => 15,
                'created' => '2018-09-18 09:20:02',
                'modified' => '2018-09-18 09:20:02'
            ],
            [
                'id' => 153,
                'card_id' => 1006,
                'card_group_id' => 15,
                'created' => '2018-09-18 09:20:02',
                'modified' => '2018-09-18 09:20:02'
            ],
            [
                'id' => 154,
                'card_id' => 1007,
                'card_group_id' => 15,
                'created' => '2018-09-18 09:20:02',
                'modified' => '2018-09-18 09:20:02'
            ],
            [
                'id' => 155,
                'card_id' => 1008,
                'card_group_id' => 15,
                'created' => '2018-09-18 09:20:02',
                'modified' => '2018-09-18 09:20:02'
            ],
            [
                'id' => 156,
                'card_id' => 1009,
                'card_group_id' => 15,
                'created' => '2018-09-18 09:20:02',
                'modified' => '2018-09-18 09:20:02'
            ],
            [
                'id' => 157,
                'card_id' => 1010,
                'card_group_id' => 15,
                'created' => '2018-09-18 09:20:02',
                'modified' => '2018-09-18 09:20:02'
            ],
            [
                'id' => 158,
                'card_id' => 1011,
                'card_group_id' => 15,
                'created' => '2018-09-18 09:20:02',
                'modified' => '2018-09-18 09:20:02'
            ],
            [
                'id' => 159,
                'card_id' => 1012,
                'card_group_id' => 15,
                'created' => '2018-09-18 09:20:02',
                'modified' => '2018-09-18 09:20:02'
            ],
            [
                'id' => 160,
                'card_id' => 1013,
                'card_group_id' => 15,
                'created' => '2018-09-18 09:20:02',
                'modified' => '2018-09-18 09:20:02'
            ],
            [
                'id' => 161,
                'card_id' => 1014,
                'card_group_id' => 15,
                'created' => '2018-09-18 09:20:02',
                'modified' => '2018-09-18 09:20:02'
            ],
            [
                'id' => 162,
                'card_id' => 1015,
                'card_group_id' => 15,
                'created' => '2018-09-18 09:20:02',
                'modified' => '2018-09-18 09:20:02'
            ],
            [
                'id' => 183,
                'card_id' => 4096,
                'card_group_id' => 17,
                'created' => '2018-09-18 11:25:08',
                'modified' => '2018-09-18 11:25:08'
            ],
            [
                'id' => 184,
                'card_id' => 4097,
                'card_group_id' => 17,
                'created' => '2018-09-18 11:25:08',
                'modified' => '2018-09-18 11:25:08'
            ],
            [
                'id' => 185,
                'card_id' => 4098,
                'card_group_id' => 17,
                'created' => '2018-09-18 11:25:08',
                'modified' => '2018-09-18 11:25:08'
            ],
            [
                'id' => 186,
                'card_id' => 4099,
                'card_group_id' => 17,
                'created' => '2018-09-18 11:25:08',
                'modified' => '2018-09-18 11:25:08'
            ],
            [
                'id' => 187,
                'card_id' => 4100,
                'card_group_id' => 17,
                'created' => '2018-09-18 11:25:08',
                'modified' => '2018-09-18 11:25:08'
            ],
            [
                'id' => 188,
                'card_id' => 4101,
                'card_group_id' => 17,
                'created' => '2018-09-18 11:25:08',
                'modified' => '2018-09-18 11:25:08'
            ],
            [
                'id' => 189,
                'card_id' => 4102,
                'card_group_id' => 17,
                'created' => '2018-09-18 11:25:08',
                'modified' => '2018-09-18 11:25:08'
            ],
            [
                'id' => 190,
                'card_id' => 4103,
                'card_group_id' => 17,
                'created' => '2018-09-18 11:25:08',
                'modified' => '2018-09-18 11:25:08'
            ],
            [
                'id' => 191,
                'card_id' => 4104,
                'card_group_id' => 17,
                'created' => '2018-09-18 11:25:08',
                'modified' => '2018-09-18 11:25:08'
            ],
            [
                'id' => 192,
                'card_id' => 4105,
                'card_group_id' => 17,
                'created' => '2018-09-18 11:25:08',
                'modified' => '2018-09-18 11:25:08'
            ],
            [
                'id' => 221,
                'card_id' => 1309,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 222,
                'card_id' => 1307,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 223,
                'card_id' => 1308,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 224,
                'card_id' => 1312,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 225,
                'card_id' => 1310,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 226,
                'card_id' => 1311,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 227,
                'card_id' => 1315,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 228,
                'card_id' => 1313,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 229,
                'card_id' => 1314,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 230,
                'card_id' => 1321,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 231,
                'card_id' => 1319,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 232,
                'card_id' => 1320,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 233,
                'card_id' => 1327,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 234,
                'card_id' => 1325,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 235,
                'card_id' => 1326,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 236,
                'card_id' => 1351,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 237,
                'card_id' => 1349,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 238,
                'card_id' => 1350,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 239,
                'card_id' => 1357,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 240,
                'card_id' => 1355,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 241,
                'card_id' => 1356,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 242,
                'card_id' => 1336,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 243,
                'card_id' => 4147,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 244,
                'card_id' => 1335,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 245,
                'card_id' => 1333,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 246,
                'card_id' => 1331,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 247,
                'card_id' => 1332,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 248,
                'card_id' => 4146,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 249,
                'card_id' => 1328,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 250,
                'card_id' => 1329,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 251,
                'card_id' => 1342,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 252,
                'card_id' => 1340,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 253,
                'card_id' => 1341,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 254,
                'card_id' => 1272,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 255,
                'card_id' => 300,
                'card_group_id' => 19,
                'created' => '2018-09-21 11:12:09',
                'modified' => '2018-09-21 11:12:09'
            ],
            [
                'id' => 256,
                'card_id' => 1403,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 257,
                'card_id' => 568,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 258,
                'card_id' => 1404,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 259,
                'card_id' => 553,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 260,
                'card_id' => 1411,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 261,
                'card_id' => 471,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 262,
                'card_id' => 1412,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 263,
                'card_id' => 557,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 264,
                'card_id' => 445,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 265,
                'card_id' => 1408,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 266,
                'card_id' => 1394,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 267,
                'card_id' => 1409,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 268,
                'card_id' => 1396,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 269,
                'card_id' => 1410,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 270,
                'card_id' => 1398,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 271,
                'card_id' => 1407,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 272,
                'card_id' => 1392,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 273,
                'card_id' => 1406,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 274,
                'card_id' => 1391,
                'card_group_id' => 20,
                'created' => '2018-09-24 05:40:18',
                'modified' => '2018-09-24 05:40:18'
            ],
            [
                'id' => 275,
                'card_id' => 1413,
                'card_group_id' => 21,
                'created' => '2018-09-24 05:48:33',
                'modified' => '2018-09-24 05:48:33'
            ],
            [
                'id' => 276,
                'card_id' => 1414,
                'card_group_id' => 21,
                'created' => '2018-09-24 05:48:33',
                'modified' => '2018-09-24 05:48:33'
            ],
            [
                'id' => 277,
                'card_id' => 1073,
                'card_group_id' => 21,
                'created' => '2018-09-24 05:48:33',
                'modified' => '2018-09-24 05:48:33'
            ],
            [
                'id' => 278,
                'card_id' => 1422,
                'card_group_id' => 21,
                'created' => '2018-09-24 05:48:33',
                'modified' => '2018-09-24 05:48:33'
            ],
            [
                'id' => 279,
                'card_id' => 1418,
                'card_group_id' => 21,
                'created' => '2018-09-24 05:48:33',
                'modified' => '2018-09-24 05:48:33'
            ],
            [
                'id' => 280,
                'card_id' => 407,
                'card_group_id' => 21,
                'created' => '2018-09-24 05:48:33',
                'modified' => '2018-09-24 05:48:33'
            ],
            [
                'id' => 281,
                'card_id' => 1420,
                'card_group_id' => 21,
                'created' => '2018-09-24 05:48:33',
                'modified' => '2018-09-24 05:48:33'
            ],
            [
                'id' => 282,
                'card_id' => 1417,
                'card_group_id' => 21,
                'created' => '2018-09-24 05:48:33',
                'modified' => '2018-09-24 05:48:33'
            ],
            [
                'id' => 283,
                'card_id' => 1416,
                'card_group_id' => 21,
                'created' => '2018-09-24 05:48:33',
                'modified' => '2018-09-24 05:48:33'
            ],
            [
                'id' => 284,
                'card_id' => 568,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:45',
                'modified' => '2018-09-24 05:59:45'
            ],
            [
                'id' => 285,
                'card_id' => 1403,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:45',
                'modified' => '2018-09-24 05:59:45'
            ],
            [
                'id' => 286,
                'card_id' => 1413,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:45',
                'modified' => '2018-09-24 05:59:45'
            ],
            [
                'id' => 287,
                'card_id' => 553,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:45',
                'modified' => '2018-09-24 05:59:45'
            ],
            [
                'id' => 288,
                'card_id' => 1404,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:45',
                'modified' => '2018-09-24 05:59:45'
            ],
            [
                'id' => 289,
                'card_id' => 1414,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:45',
                'modified' => '2018-09-24 05:59:45'
            ],
            [
                'id' => 290,
                'card_id' => 471,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 291,
                'card_id' => 1411,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 292,
                'card_id' => 1073,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 293,
                'card_id' => 557,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 294,
                'card_id' => 445,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 295,
                'card_id' => 1412,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 296,
                'card_id' => 1422,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 297,
                'card_id' => 1394,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 298,
                'card_id' => 1408,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 299,
                'card_id' => 1418,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 300,
                'card_id' => 1396,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 301,
                'card_id' => 1409,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 302,
                'card_id' => 407,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 303,
                'card_id' => 1398,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 304,
                'card_id' => 1410,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 305,
                'card_id' => 1420,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 306,
                'card_id' => 1392,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 307,
                'card_id' => 1407,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 308,
                'card_id' => 1417,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 309,
                'card_id' => 1391,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 310,
                'card_id' => 1406,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 311,
                'card_id' => 1416,
                'card_group_id' => 22,
                'created' => '2018-09-24 05:59:46',
                'modified' => '2018-09-24 05:59:46'
            ],
            [
                'id' => 312,
                'card_id' => 453,
                'card_group_id' => 23,
                'created' => '2018-09-28 05:53:29',
                'modified' => '2018-09-28 05:53:29'
            ],
            [
                'id' => 313,
                'card_id' => 575,
                'card_group_id' => 23,
                'created' => '2018-09-28 05:53:29',
                'modified' => '2018-09-28 05:53:29'
            ],
            [
                'id' => 314,
                'card_id' => 2057,
                'card_group_id' => 23,
                'created' => '2018-09-28 05:53:29',
                'modified' => '2018-09-28 05:53:29'
            ],
            [
                'id' => 315,
                'card_id' => 2053,
                'card_group_id' => 23,
                'created' => '2018-09-28 05:53:29',
                'modified' => '2018-09-28 05:53:29'
            ],
            [
                'id' => 316,
                'card_id' => 2055,
                'card_group_id' => 23,
                'created' => '2018-09-28 05:53:29',
                'modified' => '2018-09-28 05:53:29'
            ],
            [
                'id' => 317,
                'card_id' => 2059,
                'card_group_id' => 23,
                'created' => '2018-09-28 05:53:29',
                'modified' => '2018-09-28 05:53:29'
            ],
            [
                'id' => 318,
                'card_id' => 2051,
                'card_group_id' => 23,
                'created' => '2018-09-28 05:53:29',
                'modified' => '2018-09-28 05:53:29'
            ],
            [
                'id' => 319,
                'card_id' => 2123,
                'card_group_id' => 23,
                'created' => '2018-09-28 05:53:29',
                'modified' => '2018-09-28 05:53:29'
            ],
            [
                'id' => 320,
                'card_id' => 2049,
                'card_group_id' => 23,
                'created' => '2018-09-28 05:53:29',
                'modified' => '2018-09-28 05:53:29'
            ],
            [
                'id' => 321,
                'card_id' => 2052,
                'card_group_id' => 23,
                'created' => '2018-09-28 05:53:29',
                'modified' => '2018-09-28 05:53:29'
            ],
            [
                'id' => 322,
                'card_id' => 544,
                'card_group_id' => 23,
                'created' => '2018-09-28 05:53:29',
                'modified' => '2018-09-28 05:53:29'
            ],
            [
                'id' => 323,
                'card_id' => 2127,
                'card_group_id' => 23,
                'created' => '2018-09-28 05:53:29',
                'modified' => '2018-09-28 05:53:29'
            ],
            [
                'id' => 324,
                'card_id' => 2058,
                'card_group_id' => 23,
                'created' => '2018-09-28 05:53:29',
                'modified' => '2018-09-28 05:53:29'
            ],
            [
                'id' => 333,
                'card_id' => 691,
                'card_group_id' => 25,
                'created' => '2018-09-28 12:19:51',
                'modified' => '2018-09-28 12:19:51'
            ],
            [
                'id' => 334,
                'card_id' => 2252,
                'card_group_id' => 25,
                'created' => '2018-09-28 12:19:51',
                'modified' => '2018-09-28 12:19:51'
            ],
            [
                'id' => 335,
                'card_id' => 2253,
                'card_group_id' => 25,
                'created' => '2018-09-28 12:19:51',
                'modified' => '2018-09-28 12:19:51'
            ],
            [
                'id' => 336,
                'card_id' => 2255,
                'card_group_id' => 25,
                'created' => '2018-09-28 12:19:51',
                'modified' => '2018-09-28 12:19:51'
            ],
            [
                'id' => 337,
                'card_id' => 2257,
                'card_group_id' => 26,
                'created' => '2018-09-28 12:23:41',
                'modified' => '2018-09-28 12:23:41'
            ],
            [
                'id' => 338,
                'card_id' => 2254,
                'card_group_id' => 26,
                'created' => '2018-09-28 12:23:41',
                'modified' => '2018-09-28 12:23:41'
            ],
            [
                'id' => 339,
                'card_id' => 2256,
                'card_group_id' => 26,
                'created' => '2018-09-28 12:23:41',
                'modified' => '2018-09-28 12:23:41'
            ],
            [
                'id' => 340,
                'card_id' => 2258,
                'card_group_id' => 26,
                'created' => '2018-09-28 12:23:41',
                'modified' => '2018-09-28 12:23:41'
            ],
            [
                'id' => 341,
                'card_id' => 3019,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 342,
                'card_id' => 3020,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 343,
                'card_id' => 3021,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 344,
                'card_id' => 3022,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 345,
                'card_id' => 3023,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 346,
                'card_id' => 234,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 347,
                'card_id' => 3025,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 348,
                'card_id' => 790,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 349,
                'card_id' => 920,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 350,
                'card_id' => 3028,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 351,
                'card_id' => 2342,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 352,
                'card_id' => 3030,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 353,
                'card_id' => 3031,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 354,
                'card_id' => 3032,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 355,
                'card_id' => 3033,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 356,
                'card_id' => 3034,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 357,
                'card_id' => 3035,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 358,
                'card_id' => 3036,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 359,
                'card_id' => 3037,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 360,
                'card_id' => 3038,
                'card_group_id' => 27,
                'created' => '2018-10-19 07:59:12',
                'modified' => '2018-10-19 07:59:12'
            ],
            [
                'id' => 361,
                'card_id' => 3702,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 362,
                'card_id' => 3705,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 363,
                'card_id' => 3696,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 364,
                'card_id' => 3699,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 365,
                'card_id' => 3708,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 366,
                'card_id' => 3717,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 367,
                'card_id' => 3711,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 368,
                'card_id' => 3714,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 369,
                'card_id' => 3723,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 370,
                'card_id' => 3720,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 371,
                'card_id' => 3729,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 372,
                'card_id' => 3726,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 373,
                'card_id' => 3741,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 374,
                'card_id' => 3738,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 375,
                'card_id' => 3732,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 376,
                'card_id' => 3735,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 377,
                'card_id' => 3750,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 378,
                'card_id' => 3744,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 379,
                'card_id' => 3747,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 380,
                'card_id' => 3759,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 381,
                'card_id' => 3762,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 382,
                'card_id' => 3753,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 383,
                'card_id' => 3756,
                'card_group_id' => 28,
                'created' => '2018-10-30 13:03:05',
                'modified' => '2018-10-30 13:03:05'
            ],
            [
                'id' => 384,
                'card_id' => 3703,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 385,
                'card_id' => 3706,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 386,
                'card_id' => 3697,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 387,
                'card_id' => 3700,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 388,
                'card_id' => 3709,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 389,
                'card_id' => 3718,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 390,
                'card_id' => 3712,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 391,
                'card_id' => 3715,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 392,
                'card_id' => 3724,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 393,
                'card_id' => 3721,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 394,
                'card_id' => 3730,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 395,
                'card_id' => 3727,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 396,
                'card_id' => 3742,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 397,
                'card_id' => 3739,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 398,
                'card_id' => 3733,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 399,
                'card_id' => 3736,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 400,
                'card_id' => 3751,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 401,
                'card_id' => 3745,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 402,
                'card_id' => 3748,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 403,
                'card_id' => 3754,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 404,
                'card_id' => 3760,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 405,
                'card_id' => 3763,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 406,
                'card_id' => 3757,
                'card_group_id' => 29,
                'created' => '2018-10-30 13:08:09',
                'modified' => '2018-10-30 13:08:09'
            ],
            [
                'id' => 407,
                'card_id' => 3771,
                'card_group_id' => 30,
                'created' => '2018-10-31 07:42:19',
                'modified' => '2018-10-31 07:42:19'
            ],
            [
                'id' => 408,
                'card_id' => 3774,
                'card_group_id' => 30,
                'created' => '2018-10-31 07:42:19',
                'modified' => '2018-10-31 07:42:19'
            ],
            [
                'id' => 409,
                'card_id' => 3768,
                'card_group_id' => 30,
                'created' => '2018-10-31 07:42:19',
                'modified' => '2018-10-31 07:42:19'
            ],
            [
                'id' => 410,
                'card_id' => 3765,
                'card_group_id' => 30,
                'created' => '2018-10-31 07:42:19',
                'modified' => '2018-10-31 07:42:19'
            ],
            [
                'id' => 411,
                'card_id' => 3780,
                'card_group_id' => 30,
                'created' => '2018-10-31 07:42:19',
                'modified' => '2018-10-31 07:42:19'
            ],
            [
                'id' => 412,
                'card_id' => 3783,
                'card_group_id' => 30,
                'created' => '2018-10-31 07:42:19',
                'modified' => '2018-10-31 07:42:19'
            ],
            [
                'id' => 413,
                'card_id' => 3777,
                'card_group_id' => 30,
                'created' => '2018-10-31 07:42:19',
                'modified' => '2018-10-31 07:42:19'
            ],
            [
                'id' => 414,
                'card_id' => 3786,
                'card_group_id' => 30,
                'created' => '2018-10-31 07:42:19',
                'modified' => '2018-10-31 07:42:19'
            ],
            [
                'id' => 415,
                'card_id' => 3798,
                'card_group_id' => 30,
                'created' => '2018-10-31 07:42:19',
                'modified' => '2018-10-31 07:42:19'
            ],
            [
                'id' => 416,
                'card_id' => 3789,
                'card_group_id' => 30,
                'created' => '2018-10-31 07:42:19',
                'modified' => '2018-10-31 07:42:19'
            ],
            [
                'id' => 417,
                'card_id' => 3792,
                'card_group_id' => 30,
                'created' => '2018-10-31 07:42:19',
                'modified' => '2018-10-31 07:42:19'
            ],
            [
                'id' => 418,
                'card_id' => 3795,
                'card_group_id' => 30,
                'created' => '2018-10-31 07:42:19',
                'modified' => '2018-10-31 07:42:19'
            ],
            [
                'id' => 419,
                'card_id' => 3801,
                'card_group_id' => 30,
                'created' => '2018-10-31 07:42:19',
                'modified' => '2018-10-31 07:42:19'
            ],
            [
                'id' => 420,
                'card_id' => 3807,
                'card_group_id' => 30,
                'created' => '2018-10-31 07:42:19',
                'modified' => '2018-10-31 07:42:19'
            ],
            [
                'id' => 421,
                'card_id' => 3810,
                'card_group_id' => 30,
                'created' => '2018-10-31 07:42:19',
                'modified' => '2018-10-31 07:42:19'
            ],
            [
                'id' => 422,
                'card_id' => 4445,
                'card_group_id' => 30,
                'created' => '2018-10-31 07:42:19',
                'modified' => '2018-10-31 07:42:19'
            ],
            [
                'id' => 423,
                'card_id' => 3772,
                'card_group_id' => 31,
                'created' => '2018-10-31 07:44:18',
                'modified' => '2018-10-31 07:44:18'
            ],
            [
                'id' => 424,
                'card_id' => 3775,
                'card_group_id' => 31,
                'created' => '2018-10-31 07:44:18',
                'modified' => '2018-10-31 07:44:18'
            ],
            [
                'id' => 425,
                'card_id' => 3769,
                'card_group_id' => 31,
                'created' => '2018-10-31 07:44:18',
                'modified' => '2018-10-31 07:44:18'
            ],
            [
                'id' => 426,
                'card_id' => 3766,
                'card_group_id' => 31,
                'created' => '2018-10-31 07:44:18',
                'modified' => '2018-10-31 07:44:18'
            ],
            [
                'id' => 427,
                'card_id' => 3781,
                'card_group_id' => 31,
                'created' => '2018-10-31 07:44:18',
                'modified' => '2018-10-31 07:44:18'
            ],
            [
                'id' => 428,
                'card_id' => 3784,
                'card_group_id' => 31,
                'created' => '2018-10-31 07:44:18',
                'modified' => '2018-10-31 07:44:18'
            ],
            [
                'id' => 429,
                'card_id' => 3778,
                'card_group_id' => 31,
                'created' => '2018-10-31 07:44:18',
                'modified' => '2018-10-31 07:44:18'
            ],
            [
                'id' => 430,
                'card_id' => 3787,
                'card_group_id' => 31,
                'created' => '2018-10-31 07:44:18',
                'modified' => '2018-10-31 07:44:18'
            ],
            [
                'id' => 431,
                'card_id' => 3799,
                'card_group_id' => 31,
                'created' => '2018-10-31 07:44:18',
                'modified' => '2018-10-31 07:44:18'
            ],
            [
                'id' => 432,
                'card_id' => 3790,
                'card_group_id' => 31,
                'created' => '2018-10-31 07:44:18',
                'modified' => '2018-10-31 07:44:18'
            ],
            [
                'id' => 433,
                'card_id' => 3793,
                'card_group_id' => 31,
                'created' => '2018-10-31 07:44:18',
                'modified' => '2018-10-31 07:44:18'
            ],
            [
                'id' => 434,
                'card_id' => 3796,
                'card_group_id' => 31,
                'created' => '2018-10-31 07:44:18',
                'modified' => '2018-10-31 07:44:18'
            ],
            [
                'id' => 435,
                'card_id' => 3802,
                'card_group_id' => 31,
                'created' => '2018-10-31 07:44:18',
                'modified' => '2018-10-31 07:44:18'
            ],
            [
                'id' => 436,
                'card_id' => 3808,
                'card_group_id' => 31,
                'created' => '2018-10-31 07:44:18',
                'modified' => '2018-10-31 07:44:18'
            ],
            [
                'id' => 437,
                'card_id' => 3811,
                'card_group_id' => 31,
                'created' => '2018-10-31 07:44:18',
                'modified' => '2018-10-31 07:44:18'
            ],
            [
                'id' => 438,
                'card_id' => 4446,
                'card_group_id' => 31,
                'created' => '2018-10-31 07:44:18',
                'modified' => '2018-10-31 07:44:18'
            ],
            [
                'id' => 457,
                'card_id' => 8,
                'card_group_id' => 34,
                'created' => '2019-03-06 18:28:53',
                'modified' => '2019-03-06 18:28:53'
            ],
            [
                'id' => 458,
                'card_id' => 10,
                'card_group_id' => 34,
                'created' => '2019-03-06 18:28:53',
                'modified' => '2019-03-06 18:28:53'
            ],
            [
                'id' => 459,
                'card_id' => 4,
                'card_group_id' => 34,
                'created' => '2019-03-06 18:28:53',
                'modified' => '2019-03-06 18:28:53'
            ],
            [
                'id' => 460,
                'card_id' => 7,
                'card_group_id' => 34,
                'created' => '2019-03-06 18:28:53',
                'modified' => '2019-03-06 18:28:53'
            ],
            [
                'id' => 461,
                'card_id' => 9,
                'card_group_id' => 34,
                'created' => '2019-03-06 18:28:53',
                'modified' => '2019-03-06 18:28:53'
            ],
            [
                'id' => 462,
                'card_id' => 4257,
                'card_group_id' => 34,
                'created' => '2019-03-06 18:28:53',
                'modified' => '2019-03-06 18:28:53'
            ],
            [
                'id' => 463,
                'card_id' => 5,
                'card_group_id' => 34,
                'created' => '2019-03-06 18:28:53',
                'modified' => '2019-03-06 18:28:53'
            ],
            [
                'id' => 464,
                'card_id' => 3,
                'card_group_id' => 34,
                'created' => '2019-03-06 18:28:53',
                'modified' => '2019-03-06 18:28:53'
            ],
        ];
        parent::init();
    }
}
