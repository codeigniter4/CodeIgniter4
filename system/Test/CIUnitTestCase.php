<?php namespace CodeIgniter\Test;

use PHPUnit_Framework_TestCase;
use CodeIgniter\Log\TestLogger;

class CIUnitTestCase extends PHPUnit_Framework_TestCase
{
	use ReflectionHelper;

	/**
	 * Custom function to hook into CodeIgniter's Logging mechanism
	 * to check if certain messages were logged during code execution.
	 *
	 * @param string $level
	 * @param null   $expectedMessage
	 */
	public function assertLogged(string $level, $expectedMessage = null)
	{
		$result = TestLogger::didLog($level, $expectedMessage);

		$this->assertTrue($result);
	}

	//--------------------------------------------------------------------

}
