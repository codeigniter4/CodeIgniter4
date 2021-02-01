<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\CodeIgniter;
use CodeIgniter\Config\Factories;
use CodeIgniter\Events\Events;
use CodeIgniter\Session\Handlers\ArrayHandler;
use CodeIgniter\Test\Mock\MockCache;
use CodeIgniter\Test\Mock\MockEmail;
use CodeIgniter\Test\Mock\MockSession;
use Config\Services;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * PHPunit test case.
 */
abstract class CIUnitTestCase extends TestCase
{
	use ReflectionHelper;

	/**
	 * @var \CodeIgniter\CodeIgniter
	 */
	protected $app;

	/**
	 * Methods to run during setUp.
	 *
	 * @var array of methods
	 */
	protected $setUpMethods = [
		'resetFactories',
		'mockCache',
		'mockEmail',
		'mockSession',
	];

	/**
	 * Methods to run during tearDown.
	 *
	 * @var array of methods
	 */
	protected $tearDownMethods = [];

	//--------------------------------------------------------------------
	// Staging
	//--------------------------------------------------------------------

	/**
	 * Load the helpers.
	 */
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		helper(['url', 'test']);
	}

	protected function setUp(): void
	{
		parent::setUp();

		if (! $this->app) // @phpstan-ignore-line
		{
			$this->app = $this->createApplication();
		}

		foreach ($this->setUpMethods as $method)
		{
			$this->$method();
		}
	}

	protected function tearDown(): void
	{
		parent::tearDown();

		foreach ($this->tearDownMethods as $method)
		{
			$this->$method();
		}
	}

	//--------------------------------------------------------------------
	// Mocking
	//--------------------------------------------------------------------

	/**
	 * Resets shared instanced for all Factories components
	 */
	protected function resetFactories()
	{
		Factories::reset();
	}

	/**
	 * Resets shared instanced for all Services
	 */
	protected function resetServices()
	{
		Services::reset();
	}

	/**
	 * Injects the mock Cache driver to prevent filesystem collisions
	 */
	protected function mockCache()
	{
		Services::injectMock('cache', new MockCache());
	}

	/**
	 * Injects the mock email driver so no emails really send
	 */
	protected function mockEmail()
	{
		Services::injectMock('email', new MockEmail(config('Email')));
	}

	/**
	 * Injects the mock session driver into Services
	 */
	protected function mockSession()
	{
		$_SESSION = [];

		$config  = config('App');
		$session = new MockSession(new ArrayHandler($config, '0.0.0.0'), $config);

		Services::injectMock('session', $session);
	}

	//--------------------------------------------------------------------
	// Assertions
	//--------------------------------------------------------------------

	/**
	 * Custom function to hook into CodeIgniter's Logging mechanism
	 * to check if certain messages were logged during code execution.
	 *
	 * @param string      $level
	 * @param string|null $expectedMessage
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function assertLogged(string $level, $expectedMessage = null)
	{
		$result = TestLogger::didLog($level, $expectedMessage);

		$this->assertTrue($result);
		return $result;
	}

	/**
	 * Hooks into CodeIgniter's Events system to check if a specific
	 * event was triggered or not.
	 *
	 * @param string $eventName
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function assertEventTriggered(string $eventName): bool
	{
		$found     = false;
		$eventName = strtolower($eventName);

		foreach (Events::getPerformanceLogs() as $log)
		{
			if ($log['event'] !== $eventName)
			{
				continue;
			}

			$found = true;
			break;
		}

		$this->assertTrue($found);
		return $found;
	}

	/**
	 * Hooks into xdebug's headers capture, looking for a specific header
	 * emitted
	 *
	 * @param string  $header     The leading portion of the header we are looking for
	 * @param boolean $ignoreCase
	 *
	 * @throws Exception
	 */
	public function assertHeaderEmitted(string $header, bool $ignoreCase = false): void
	{
		$found = false;

		if (! function_exists('xdebug_get_headers'))
		{
			$this->markTestSkipped('XDebug not found.');
		}

		foreach (xdebug_get_headers() as $emitted)
		{
			$found = $ignoreCase ?
					(stripos($emitted, $header) === 0) :
					(strpos($emitted, $header) === 0);
			if ($found)
			{
				break;
			}
		}

		$this->assertTrue($found, "Didn't find header for {$header}");
	}

	/**
	 * Hooks into xdebug's headers capture, looking for a specific header
	 * emitted
	 *
	 * @param string  $header     The leading portion of the header we don't want to find
	 * @param boolean $ignoreCase
	 *
	 * @throws Exception
	 */
	public function assertHeaderNotEmitted(string $header, bool $ignoreCase = false): void
	{
		$found = false;

		if (! function_exists('xdebug_get_headers'))
		{
			$this->markTestSkipped('XDebug not found.');
		}

		foreach (xdebug_get_headers() as $emitted)
		{
			$found = $ignoreCase ?
					(stripos($emitted, $header) === 0) :
					(strpos($emitted, $header) === 0);
			if ($found)
			{
				break;
			}
		}

		$success = ! $found;
		$this->assertTrue($success, "Found header for {$header}");
	}

	/**
	 * Custom function to test that two values are "close enough".
	 * This is intended for extended execution time testing,
	 * where the result is close but not exactly equal to the
	 * expected time, for reasons beyond our control.
	 *
	 * @param integer $expected
	 * @param mixed   $actual
	 * @param string  $message
	 * @param integer $tolerance
	 *
	 * @throws Exception
	 */
	public function assertCloseEnough(int $expected, $actual, string $message = '', int $tolerance = 1)
	{
		$difference = abs($expected - (int) floor($actual));

		$this->assertLessThanOrEqual($tolerance, $difference, $message);
	}

	/**
	 * Custom function to test that two values are "close enough".
	 * This is intended for extended execution time testing,
	 * where the result is close but not exactly equal to the
	 * expected time, for reasons beyond our control.
	 *
	 * @param mixed   $expected
	 * @param mixed   $actual
	 * @param string  $message
	 * @param integer $tolerance
	 *
	 * @return void|boolean
	 * @throws Exception
	 */
	public function assertCloseEnoughString($expected, $actual, string $message = '', int $tolerance = 1)
	{
		$expected = (string) $expected;
		$actual   = (string) $actual;
		if (strlen($expected) !== strlen($actual))
		{
			return false;
		}

		try
		{
			$expected   = (int) substr($expected, -2);
			$actual     = (int) substr($actual, -2);
			$difference = abs($expected - $actual);

			$this->assertLessThanOrEqual($tolerance, $difference, $message);
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	//--------------------------------------------------------------------
	// Utility
	//--------------------------------------------------------------------

	/**
	 * Loads up an instance of CodeIgniter
	 * and gets the environment setup.
	 *
	 * @return CodeIgniter
	 */
	protected function createApplication()
	{
		$path = __DIR__ . '/../bootstrap.php';
		$path = realpath($path) ?: $path;
		return require $path;
	}

	/**
	 * Return first matching emitted header.
	 *
	 * @param string  $header     Identifier of the header of interest
	 * @param boolean $ignoreCase
	 *
	 * @return string|null The value of the header found, null if not found
	 */
	protected function getHeaderEmitted(string $header, bool $ignoreCase = false): ?string
	{
		$found = false;

		if (! function_exists('xdebug_get_headers'))
		{
			$this->markTestSkipped('XDebug not found.');
		}

		foreach (xdebug_get_headers() as $emitted)
		{
			$found = $ignoreCase ?
					(stripos($emitted, $header) === 0) :
					(strpos($emitted, $header) === 0);
			if ($found)
			{
				return $emitted;
			}
		}

		return null;
	}
}
