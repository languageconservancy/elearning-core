<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 *
 */
class UsersFixture extends TestFixture
{

	/**
	 * Fields
	 *
	 * @var array
	 */
	// @codingStandardsIgnoreStart
	public $fields = [
		'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => true, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
		'email' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'latin1_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'password' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'latin1_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'name' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'latin1_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'dob' => ['type' => 'date', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'google_id' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'latin1_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'google_status' => ['type' => 'string', 'length' => null, 'null' => true, 'default' => '0', 'collate' => 'latin1_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'fb_id' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'latin1_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'fb_status' => ['type' => 'string', 'length' => null, 'null' => true, 'default' => '0', 'collate' => 'latin1_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'clever_id' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'latin1_general_ci', 'comment' => 'User\'s Clever ID if they use Clever', 'precision' => null, 'fixed' => null],
		'role_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => true, 'null' => true, 'default' => '3', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'learningspeed_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => true, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'learningpath_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => true, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'last_logged' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'is_active' => ['type' => 'string', 'length' => null, 'null' => true, 'default' => '1', 'collate' => 'latin1_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'complete_findfriend_page' => ['type' => 'string', 'length' => null, 'null' => true, 'default' => '0', 'collate' => 'latin1_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'is_delete' => ['type' => 'string', 'length' => null, 'null' => true, 'default' => '0', 'collate' => 'latin1_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'registered' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'_indexes' => [
			'FK_users_roles' => ['type' => 'index', 'columns' => ['role_id'], 'length' => []],
			'FK_users_learningspeed' => ['type' => 'index', 'columns' => ['learningspeed_id'], 'length' => []],
			'FK_users_learningpaths' => ['type' => 'index', 'columns' => ['learningpath_id'], 'length' => []],
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
				'email' => 'test@gmail.com',
				'password' => '$2y$10$vojg.AuEC4FK/vWypkDosu3ZPGUutn4btAx6gDki4HJAPTOvJEW8K', /*test*/
				'name' => 'Test User',
				'dob' => '2021-08-03',
				'google_id' => null,
				'google_status' => '0',
				'fb_id' => null,
				'fb_status' => '0',
				'clever_id' => null,
				'role_id' => '2',
				'learningspeed_id' => '1',
				'learningpath_id' => '2',
				'last_logged' => '2021-08-03 13:36:36',
				'is_active' => '1',
				'complete_findfriend_page' => 'Lorem ipsum dolor sit amet',
				'is_delete' => '0',
				'registered' => '2021-08-03 13:36:36',
				'created' => '2021-08-03 13:36:36',
				'modified' => '2021-08-03 13:36:36'
			],
		];
		parent::init();
	}
}
