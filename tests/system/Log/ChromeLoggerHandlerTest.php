<?php namespace CodeIgniter\Log\Handlers;

use Config\MockLogger as LoggerConfig;
use CodeIgniter\Services;

class ChromeLoggerHandlerTest extends \CIUnitTestCase
{

	public function setUp()
	{
		
	}

	//--------------------------------------------------------------------

	public function testCanHandleLogLevel()
	{
		$config = new LoggerConfig();
		$config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] = ['critical'];

		$logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
		$this->assertFalse($logger->canHandle('foo'));
	}

	//--------------------------------------------------------------------

	public function testHandle()
	{
		$config = new LoggerConfig();
		$config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] = ['critical'];

		$logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
		$this->assertTrue($logger->handle("warning", "This a log test"));
	}

	//--------------------------------------------------------------------

	public function testSendLogs()
	{
		$config = new LoggerConfig();
		$config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] = ['critical'];

		$logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
		$logger->sendLogs();

		$response = Services::response(null, true);

		$this->assertTrue($response->hasHeader('X-ChromeLogger-Data'));
	}

	//--------------------------------------------------------------------

	public function testSetDateFormat()
	{
		$config = new LoggerConfig();
		$config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] = ['critical'];

		$logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
		$result = $logger->setDateFormat('F j, Y');

		$this->assertObjectHasAttribute('dateFormat', $result);
		$this->assertObjectHasAttribute('dateFormat', $logger);
	}

	//--------------------------------------------------------------------

	public function testErrorLevelConversion()
	{
		$config = new LoggerConfig();
		$logger = new MockChromeHandler((array) $config);
		$logger->handle("emergency", "This a log test");

		$result = $logger->peekaboo();

		$this->assertEquals('This a log test', $result[0]);
		$this->assertEquals("error", $result[2]);
	}

	//--------------------------------------------------------------------

	public function testFormatObject()
	{
		$config = new LoggerConfig();
		$logger = new MockChromeHandler((array) $config);
		
		$nasty->code = 494;
		$nasty->message = 'Where did it go?';
		$logger->handle("emergency", $nasty);

		$result = $logger->peekaboo();

		$this->assertEquals('stdClass', $result[0]['___class_name']);
	}

}
