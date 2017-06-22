<?php namespace CodeIgniter\Log\Handlers;

use Config\MockLogger as LoggerConfig;
use Psr\Log\LogLevel;
use CodeIgniter\Log\Handlers\TestHandler;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\ResponseInterface;
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
                $config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] =  ['critical'];
                
		$logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
                $this->assertFalse($logger->canHandle('foo'));
	}
        
        //--------------------------------------------------------------------
        
        public function testHandle()
        {
                $config = new LoggerConfig();
                $config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] =  ['critical'];
                
		$logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
                $this->assertTrue($logger->handle("warning", "This a log test") );
        }
        
        //--------------------------------------------------------------------
        
        public function testSendLogs()
        {       
                $config = new LoggerConfig();
                $config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] =  ['critical'];
                
		$logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
                $logger->sendLogs();
                
                $response = Services::response(null, true);
                
                $this->assertTrue($response->hasHeader('X-ChromeLogger-Data'));
        }
        
        //--------------------------------------------------------------------
        
        public function testSetDateFormat()
        {            
                $config = new LoggerConfig();
                $config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] =  ['critical'];
                
		$logger = new ChromeLoggerHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
                $result = $logger->setDateFormat('F j, Y');
                
                $this->assertObjectHasAttribute('dateFormat', $result);
                $this->assertObjectHasAttribute('dateFormat', $logger);
        }
}
