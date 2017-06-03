<?php namespace CodeIgniter\Log\Handlers;

use Config\MockLogger as LoggerConfig;
use Psr\Log\LogLevel;
use CodeIgniter\Log\Handlers\TestHandler;

class FileHandlerTest extends \CIUnitTestCase
{
	public function setUp()
	{
	}

	//--------------------------------------------------------------------
        
        public function testHandle()
        {
                $config = new LoggerConfig();
                $config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] =  ['critical'];
                
                $logger = new FileHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
                $logger->setDateFormat("Y-m-d H:i:s:u");
                $this->assertTrue($logger->handle("warning", "This is a test log") );
        }
}
