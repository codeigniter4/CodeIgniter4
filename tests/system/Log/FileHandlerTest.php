<?php
namespace CodeIgniter\Log\Handlers;

use CodeIgniter\Test\Mock\MockLogger as LoggerConfig;
use CodeIgniter\Test\Mock\MockFileLogger;
use org\bovigo\vfs\vfsStream;

class FileHandlerTest extends \CodeIgniter\Test\CIUnitTestCase
{

	protected function setUp(): void
	{
		parent::setUp();
		$this->root  = vfsStream::setup('root');
		$this->start = $this->root->url() . '/';
	}

	public function testHandle()
	{
		$config                                                                = new LoggerConfig();
		$config->handlers['Tests\Support\Log\Handlers\TestHandler']['handles'] = ['critical'];

		$logger = new MockFileLogger($config->handlers['Tests\Support\Log\Handlers\TestHandler']);
		$logger->setDateFormat('Y-m-d H:i:s:u');
		$this->assertTrue($logger->handle('warning', 'This is a test log'));
	}

	//--------------------------------------------------------------------

	public function testBasicHandle()
	{
		$config                                                                = new LoggerConfig();
		$config->path                                                          = $this->start . 'charlie/';
		$config->handlers['Tests\Support\Log\Handlers\TestHandler']['handles'] = ['critical'];
		$logger                                                                = new MockFileLogger($config->handlers['Tests\Support\Log\Handlers\TestHandler']);
		$logger->setDateFormat('Y-m-d H:i:s:u');
		$this->assertTrue($logger->handle('warning', 'This is a test log'));
	}

	public function testHandleCreateFile()
	{
		$config       = new LoggerConfig();
		$config->path = $this->start;
		$logger       = new MockFileLogger((array) $config);

		$logger->setDateFormat('Y-m-d H:i:s:u');
		$logger->handle('warning', 'This is a test log');

		$expected = 'log-' . date('Y-m-d') . '.log';
		$fp       = fopen($config->path . $expected, 'r');
		$line     = fgets($fp);
		fclose($fp);

		// did the log file get created?
		$expectedResult = 'This is a test log';
		$this->assertStringContainsString($expectedResult, $line);
	}

	public function testHandleDateTimeCorrectly()
	{
		$config       = new LoggerConfig();
		$config->path = $this->start;
		$logger       = new MockFileLogger((array) $config);

		$logger->setDateFormat('Y-m-d');
		$expected = 'log-' . date('Y-m-d') . '.log';

		$logger->handle('debug', 'Test message');
		$fp   = fopen($config->path . $expected, 'r');
		$line = fgets($fp); // and get the second line
		fclose($fp);

		$expectedResult = 'Test message';
		$this->assertStringContainsString($expectedResult, $line);
	}

}
