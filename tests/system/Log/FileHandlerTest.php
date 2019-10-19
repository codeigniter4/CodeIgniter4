<?php
namespace CodeIgniter\Log\Handlers;

use Tests\Support\Config\MockLogger as LoggerConfig;
use Tests\Support\Log\Handlers\MockFileHandler as MockFileHandler;
use org\bovigo\vfs\vfsStream;

class FileHandlerTest extends \CIUnitTestCase
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

		$logger = new MockFileHandler($config->handlers['Tests\Support\Log\Handlers\TestHandler']);
		$logger->setDateFormat('Y-m-d H:i:s:u');
		$this->assertTrue($logger->handle('warning', 'This is a test log'));
	}

	//--------------------------------------------------------------------

	public function testBasicHandle()
	{
		$config                                                                = new LoggerConfig();
		$config->path                                                          = $this->start . 'charlie/';
		$config->handlers['Tests\Support\Log\Handlers\TestHandler']['handles'] = ['critical'];
		$logger                                                                = new MockFileHandler($config->handlers['Tests\Support\Log\Handlers\TestHandler']);
		$logger->setDateFormat('Y-m-d H:i:s:u');
		$this->assertTrue($logger->handle('warning', 'This is a test log'));
	}

	public function testHandleCreateFile()
	{
		$config       = new LoggerConfig();
		$config->path = $this->start;
		$logger       = new MockFileHandler((array) $config);

		$logger->setDateFormat('Y-m-d H:i:s:u');
		$logger->handle('warning', 'This is a test log');

		$expected = 'log-' . date('Y-m-d') . '.php';
		$fp       = fopen($config->path . $expected, 'r');
		$line     = fgets($fp);
		fclose($fp);

		// did the log file get created?
		$expectedResult = "<?php defined('SYSTEMPATH') || exit('No direct script access allowed'); ?>\n";
		$this->assertEquals($expectedResult, $line);
	}

	public function testHandleDateTimeCorrectly()
	{
		$config       = new LoggerConfig();
		$config->path = $this->start;
		$logger       = new MockFileHandler((array) $config);

		$logger->setDateFormat('Y-m-d');
		$expected = 'log-' . date('Y-m-d') . '.php';

		$logger->handle('debug', 'Test message');

		$fp   = fopen($config->path . $expected, 'r');
		$line = fgets($fp); // skip opening PHP tag
		$line = fgets($fp); // skip blank line
		$line = fgets($fp); // and get the second line
		fclose($fp);

		$expectedResult = 'DEBUG - ' . date('Y-m-d') . ' --> Test message';
		$this->assertEquals($expectedResult, substr($line, 0, strlen($expectedResult)));
	}

}
