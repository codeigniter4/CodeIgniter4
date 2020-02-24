<?php namespace CodeIgniter\Log\Handlers;

use CodeIgniter\Test\Mock\MockLogger as LoggerConfig;
use CodeIgniter\Test\Mock\MockChromeLogger;
use CodeIgniter\Services;

class ChromeLoggerHandlerTest extends \CodeIgniter\Test\CIUnitTestCase
{

	public function testCanHandleLogLevel()
	{
		$config                                                              = new LoggerConfig();
		$config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] = ['critical'];

		$logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
		$this->assertFalse($logger->canHandle('foo'));
	}

	//--------------------------------------------------------------------

	public function testHandle()
	{
		$config                                                              = new LoggerConfig();
		$config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] = ['critical'];

		$logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
		$this->assertTrue($logger->handle('warning', 'This a log test'));
	}

	//--------------------------------------------------------------------

	public function testSendLogs()
	{
		$config                                                              = new LoggerConfig();
		$config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] = ['critical'];

		$logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
		$logger->sendLogs();

		$response = Services::response(null, true);

		$this->assertTrue($response->hasHeader('X-ChromeLogger-Data'));
	}

	//--------------------------------------------------------------------

	public function testSetDateFormat()
	{
		$config                                                              = new LoggerConfig();
		$config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] = ['critical'];

		$logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
		$result = $logger->setDateFormat('F j, Y');

		$this->assertObjectHasAttribute('dateFormat', $result);
		$this->assertObjectHasAttribute('dateFormat', $logger);
	}

	//--------------------------------------------------------------------

	public function testObjectMessage()
	{
		$config                                                              = new LoggerConfig();
		$config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] = ['critical'];

		$logger            = new MockChromeLogger($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
		$data              = new \stdClass();
		$data->code        = 123;
		$data->explanation = "That's no moon, it's a pumpkin";
		$result            = $logger->setDateFormat('F j, Y');

		$logger->handle('debug', $data);
		$peek = $logger->peekaboo();

		$this->assertEquals($data->explanation, $peek[0]['explanation']);
	}

}
