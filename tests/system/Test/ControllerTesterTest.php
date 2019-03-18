<?php
namespace CodeIgniter\Test;

use CodeIgniter\Log\Logger;
use Tests\Support\Config\MockLogger as LoggerConfig;

class ControllerTesterTest extends \CIUnitTestCase
{

	use ControllerTester;

	public function setUp()
	{
		parent::setUp();
	}

	public function tearDown()
	{
		parent::tearDown();
	}

	public function testBadController()
	{
		$this->expectException(\InvalidArgumentException::class);
		$logger = new Logger(new LoggerConfig());
		$result = $this->withURI('http://example.com')
				->withLogger($logger)
				->controller(\App\Controllers\NeverHeardOfIt::class)
				->execute('index');
	}

	public function testBadControllerMethod()
	{
		$this->expectException(\InvalidArgumentException::class);
		$logger = new Logger(new LoggerConfig());
		$result = $this->withURI('http://example.com')
				->withLogger($logger)
				->controller(\App\Controllers\Home::class)
				->execute('nothere');
	}

	public function testController()
	{
		$logger = new Logger(new LoggerConfig());
		$result = $this->withURI('http://example.com')
				->withLogger($logger)
				->controller(\App\Controllers\Home::class)
				->execute('index');

		$body = $result->getBody();
		$this->assertTrue($result->isOK());
	}

}
