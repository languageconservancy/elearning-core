<?php

namespace App\Test\TestCase\Controller;

use App\Controller\HelloWorldController;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\HelloWorldController Test Case.
 * Created using the bake command:
 *   ./bin/cake bake test Controller HelloWorld from the backend directory
 */
class HelloWorldControllerTest extends IntegrationTestCase
{
    /**
     * Simplest test
     *
     * @return void
     */
    public function testHelloWorld()
    {
        $helloWorldStr = 'Hello World';
        $this->assertEquals('Hello World', $helloWorldStr);
    }
}
