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
                $this->assertTrue($logger->handle("emergency", "This is a test log") );
                
                
        }

        

}
