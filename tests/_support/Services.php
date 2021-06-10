<?php

namespace CodeIgniter;

use CIUnitTestCase;
use Config\Services as ConfigServices;

/**
 * Services class for testing.
 */
class Services
{
    /**
     * Mock objects for testing which are returned if exist.
     *
     * @var array
     */
    protected static $mocks = [];

    //--------------------------------------------------------------------

    /**
     * Reset shared instances and mocks for testing.
     */
    public static function reset()
    {
        static::$mocks = [];

        CIUnitTestCase::setPrivateProperty(ConfigServices::class, 'instances', []);
    }

    //--------------------------------------------------------------------

    /**
     * Inject mock object for testing.
     *
     * @param string $name
     * @param $mock
     */
    public static function injectMock(string $name, $mock)
    {
        $name                 = strtolower($name);
        static::$mocks[$name] = $mock;
    }

    //--------------------------------------------------------------------

    /**
     * Returns a service
     *
     * @param string $name
     * @param array  $arguments
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $name = strtolower($name);

        // Returns mock if exists
        if (isset(static::$mocks[$name])) {
            return static::$mocks[$name];
        }

        if (method_exists(ConfigServices::class, $name)) {
            return ConfigServices::$name(...$arguments);
        }
    }

    //--------------------------------------------------------------------
}
