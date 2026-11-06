<?php

namespace App\Test\TestCase\Controller\Api;

use App\Controller\Api\AppController;
use App\Model\Table\ClassroomUsersTable;
use Cake\Database\Connection;
use Cake\Database\Driver\Sqlite;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class AppControllerTest extends TestCase
{
    public function testClassroomCountCountsDistinctMemberships(): void
    {
        $connection = new Connection(['driver' => Sqlite::class, 'database' => ':memory:']);
        $connection->execute(
            'CREATE TABLE classroom_users (id INTEGER PRIMARY KEY, classroom_id INTEGER, user_id INTEGER)'
        );
        $connection->execute(
            'INSERT INTO classroom_users (classroom_id, user_id) VALUES (1, 7), (1, 7), (2, 7), (3, 8)'
        );
        $classroomUsers = new ClassroomUsersTable([
            'alias' => 'ClassroomUsers',
            'table' => 'classroom_users',
            'connection' => $connection,
        ]);
        $controller = $this->getMockBuilder(AppController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getClassroomUsersTable'])
            ->getMock();
        $controller->method('getClassroomUsersTable')->willReturn($classroomUsers);

        $method = new ReflectionMethod(AppController::class, 'getClassroomCount');
        $this->assertSame(2, $method->invoke($controller, 7));
        $this->assertSame(1, $method->invoke($controller, 8));
        $this->assertSame(0, $method->invoke($controller, 9));
    }
}
