<?php namespace CodeIgniter\Log\Handlers;

use Config\MockLogger as LoggerConfig;
use Psr\Log\LogLevel;
use CodeIgniter\Log\Handlers\TestHandler;

class ChromeLoggerHandlerTest extends \CIUnitTestCase
{
	public function setUp()
	{
	}

	//--------------------------------------------------------------------

        public function testCanHandleLogLevel()
	{
		$config = new LoggerConfig();
                $config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] =  ['critical'];
		//$config->threshold = "foo";
                
		$logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
                $this->assertFalse($logger->canHandle('foo'));
	}
}
