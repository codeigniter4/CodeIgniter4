<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Test;

use CodeIgniter\Events\Events;
use Config\Paths;
use PHPUnit\Framework\TestCase;

/**
 * PHPunit test case.
 */
class CIUnitTestCase extends TestCase
{

	use ReflectionHelper;

	/**
	 * @var \CodeIgniter\CodeIgniter
	 */
	protected $app;

	protected function setUp(): void
	{
		parent::setUp();

		if (! $this->app)
		{
			$this->app = $this->createApplication();
		}

		helper('url');
	}

	/**
	 * Custom function to hook into CodeIgniter's Logging mechanism
	 * to check if certain messages were logged during code execution.
	 *
	 * @param string $level
	 * @param null   $expectedMessage
	 *
	 * @return boolean
	 * @throws \Exception
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
	 * @throws \Exception
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
	 * @throws \Exception
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
	 * @throws \Exception
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
	 * @throws \Exception
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
	 * @return boolean
	 * @throws \Exception
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
		catch (\Exception $e)
		{
			return false;
		}
	}

	/**
	 * Loads up an instance of CodeIgniter
	 * and gets the environment setup.
	 *
	 * @return \CodeIgniter\CodeIgniter
	 */
	protected function createApplication()
	{
		$paths = new Paths();

		return require realpath(__DIR__ . '/../') . '/bootstrap.php';
	}

	//--------------------------------------------------------------------
	/**
	 * Return first matching emitted header.
	 *
	 * @param string $header Identifier of the header of interest
	 * @param bool $ignoreCase
	 *
	 * @return string|null The value of the header found, null if not found
	 */
		//
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
