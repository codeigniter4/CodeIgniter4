<?php namespace Config;

// This file is loaded before setting up autoloader.
require __DIR__.'/RealServices.php';

use CIUnitTestCase;

/**
 * Services Configuration file for testing.
 *
 * We can't extend \Config\Services in APPPATH, becasuse we can't change the
 * classname.
 *
 * If you change your real \Config\Services:
 *   1. cp application/Config/Services.php tests/_support/Config/RealServices.php
 *   2. change the class name in RealServices.php to `RealServices`
 */
class Services
{
	/**
	 * Mock objects for testing which are returned if exist.
	 *
	 * @var array
	 */
	static protected $mocks = [];

	//--------------------------------------------------------------------

	/**
	 * Reset shared instances and mocks for testing.
	 */
	public static function reset()
	{
		static::$mocks = [];

		CIUnitTestCase::setPrivateProperty(RealServices::class, 'instances', []);
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
		$name = strtolower($name);
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
		if (isset(static::$mocks[$name]))
		{
			return static::$mocks[$name];
		}

		if (method_exists(RealServices::class, $name))
		{
			return RealServices::$name(...$arguments);
		}
	}

	//--------------------------------------------------------------------

}
