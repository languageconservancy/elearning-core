<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ForumPostsFixture
 *
 */
class ForumPostsFixture extends TestFixture
{

	/**
	 * Fields
	 *
	 * @var array
	 */
	// @codingStandardsIgnoreStart
	public $fields = [
		'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => true, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
		'parent_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => true, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'forum_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => true, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'user_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => true, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'flag_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => true, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'title' => ['type' => 'string', 'length' => 500, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'content' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null],
		'audio' => ['type' => 'integer', 'length' => 11, 'unsigned' => true, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'sticky' => ['type' => 'string', 'length' => null, 'null' => false, 'default' => 'N', 'collate' => 'latin1_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'is_hide' => ['type' => 'string', 'length' => null, 'null' => false, 'default' => 'N', 'collate' => 'latin1_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'entry_time' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'_indexes' => [
			'FK_forum_posts_forum_posts' => ['type' => 'index', 'columns' => ['parent_id'], 'length' => []],
			'FK_forum_posts_users' => ['type' => 'index', 'columns' => ['user_id'], 'length' => []],
			'FK_forum_posts_forums' => ['type' => 'index', 'columns' => ['forum_id'], 'length' => []],
			'FK_forum_posts_forum_flags' => ['type' => 'index', 'columns' => ['flag_id'], 'length' => []],
			'FK_forum_posts_recording_audios' => ['type' => 'index', 'columns' => ['audio'], 'length' => []],
		],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
		],
		'_options' => [
			'engine' => 'InnoDB',
			'collation' => 'latin1_general_ci'
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
				'parent_id' => 1,
				'forum_id' => 1,
				'user_id' => 1,
				'flag_id' => 1,
				'title' => 'Lorem ipsum dolor sit amet',
				'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
				'audio' => 1,
				'sticky' => 'Lorem ipsum dolor sit amet',
				'is_hide' => 'Lorem ipsum dolor sit amet',
				'entry_time' => '2021-10-27 18:08:13',
				'created' => '2021-10-27 18:08:13',
				'modified' => '2021-10-27 18:08:13'
			],
		];
		parent::init();
	}
}
