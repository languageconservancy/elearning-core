<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsersTable Test Case
 */
class UsersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UsersTable
     */
    public $Users;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Users',
        'app.Roles',
        'app.Friends',
        'app.Learningspeed',
        'app.Learningpaths',
        'app.Usersettings',
        'app.Userimages',
        'app.UserUnitActivities',
        'app.UserActivities',
        'app.ForumFlags',
        'app.ForumPosts'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Users') ? [] : ['className' => UsersTable::class];
        $this->Users = TableRegistry::getTableLocator()->get('Users', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Users);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testSaveAllData()
    {
        $user = $this->Users->newEmptyEntity();
        $userData = array(
            'name' => 'test name',
            'email' => 'test.name@test.com',
            'password' => null,
            'dob' => date('Y-m-d'),
            'google_id' => 'testgoogleid3245',
            'google_status' => '0',
            'fb_id' => 'testfbid2356',
            'fb_status' => '0',
            'clever_id' => 'testcleverid2211',
            'role_id' => 3,
            'learningspeed_id' => 1,
            'learningpath_id' => 2,
            'is_active' => '1'
        );
        $user = $this->Users->patchEntity(
            $user,
            $userData,
            ['validate' => 'ignorepassword']
        );
        $saveResult = $this->Users->save(
            $user,
            ['validate' => 'ignorepassword']
        );

        $this->assertTrue($saveResult != false);

        $dbUser = $this->Users->find()
            ->where(['google_id' => 'testgoogleid3245'])
            ->first();

        $this->assertTrue($dbUser['name'] == $user['name']);
        $this->assertTrue($dbUser['email'] == $user['email']);
        $this->assertTrue($dbUser['dob'] == $user['dob']);
        $this->assertTrue($dbUser['password'] == $user['password']);
        $this->assertTrue($dbUser['google_id'] == $user['google_id']);
        $this->assertTrue($dbUser['google_status'] == $user['google_status']);
        $this->assertTrue($dbUser['fb_id'] == $user['fb_id']);
        $this->assertTrue($dbUser['fb_status'] == $user['fb_status']);
        $this->assertTrue($dbUser['clever_id'] == $user['clever_id']);
        $this->assertTrue($dbUser['role_id'] == $user['role_id']);
        $this->assertTrue($dbUser['learningspeed_id'] == $user['learningspeed_id']);
        $this->assertTrue($dbUser['learningpath_id'] == $user['learningpath_id']);
        $this->assertTrue($dbUser['is_active'] == $user['is_active']);
    }
}
