<?php

namespace App\Test\Helpers;

class TestHelpers
{
    /**
     * Helper function that allows you to call private and protected methods
     * inside a test function.
     */
    public static function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
