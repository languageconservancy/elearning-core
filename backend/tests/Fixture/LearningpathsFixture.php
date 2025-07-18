<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * LearningpathsFixture
 *
 */
class LearningpathsFixture extends TestFixture
{

	/**
	 * Fields
	 *
	 * @var array
	 */
	// @codingStandardsIgnoreStart
	public $fields = [
		'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => true, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
		'label' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'ucs2_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'description' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'admin_access' => ['type' => 'string', 'length' => null, 'null' => false, 'default' => '1', 'collate' => 'latin1_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'user_access' => ['type' => 'string', 'length' => null, 'null' => false, 'default' => '1', 'collate' => 'latin1_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
		'image_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => true, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'owner_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => true, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'_indexes' => [
			'FK_learningpaths_files' => ['type' => 'index', 'columns' => ['image_id'], 'length' => []],
			'FK_learningpaths_users' => ['type' => 'index', 'columns' => ['owner_id'], 'length' => []],
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
		parent::init();
	}
}
