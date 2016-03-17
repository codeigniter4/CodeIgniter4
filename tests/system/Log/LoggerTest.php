<?php namespace CodeIgniter\Log;

use Config\MockLogger as LoggerConfig;
use Psr\Log\LogLevel;
use CodeIgniter\Log\Handlers\TestHandler;

class LoggerTest extends \CIUnitTestCase
{
	public function setUp()
	{
	}
	
	//--------------------------------------------------------------------
	
	public function testThrowsExceptionWithBadHandlerSettings()
	{
		$config = new LoggerConfig();
		$config->handlers = null;

		$this->setExpectedException('RuntimeException', 'LoggerConfig must provide at least one Handler.');

		$logger = new Logger($config);
	}

	//--------------------------------------------------------------------

	public function testLogThrowsExceptionOnInvalidLevel()
	{
		$config = new LoggerConfig();

		$this->setExpectedException('InvalidArgumentException', 'foo is an invalid log level.');

		$logger = new Logger($config);

		$logger->log('foo', '');
	}

	//--------------------------------------------------------------------

	public function testLogReturnsFalseWhenLogNotHandled()
	{
		$config = new LoggerConfig();
		$config->threshold = 3;

		$logger = new Logger($config);

		$this->assertFalse($logger->log('debug', ''));
	}

	//--------------------------------------------------------------------

	public function testLogActuallyLogs()
	{
		$config = new LoggerConfig();
//		$Config->handlers['TestHandler']['handles'] =  [LogLevel::CRITICAL];

		$logger = new Logger($config);

		$expected = 'DEBUG - '.date('Y-m-d').' --> Test message';

		$logger->log('debug', 'Test message');

		$logs = TestHandler::getLogs();

		$this->assertEquals(1, count($logs));
		$this->assertEquals($expected, $logs[0]);
	}

	//--------------------------------------------------------------------

	public function testLogDoesnotLogUnhandledLevels()
	{
		$config = new LoggerConfig();
		$config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] =  ['critical'];

		$logger = new Logger($config);

		$logger->log('debug', 'Test message');

		$logs = TestHandler::getLogs();

		$this->assertEquals(0, count($logs));
	}

	//--------------------------------------------------------------------

	public function testLogInterpolatesMessage()
	{
		$config = new LoggerConfig();

		$logger = new Logger($config);

		$expected = 'DEBUG - '.date('Y-m-d').' --> Test message bar baz';

		$logger->log('debug', 'Test message {foo} {bar}', ['foo' => 'bar', 'bar' => 'baz']);

		$logs = TestHandler::getLogs();

		$this->assertEquals(1, count($logs));
		$this->assertEquals($expected, $logs[0]);
	}

	//--------------------------------------------------------------------

	public function testLogInterpolatesPost()
	{
		$config = new LoggerConfig();

		$logger = new Logger($config);

		$_POST = ['foo' => 'bar'];
		$expected = 'DEBUG - '.date('Y-m-d').' --> Test message $_POST: '. print_r($_POST, true);

		$logger->log('debug', 'Test message {post_vars}');

		$logs = TestHandler::getLogs();

		$this->assertEquals(1, count($logs));
		$this->assertEquals($expected, $logs[0]);
	}

	//--------------------------------------------------------------------

	public function testLogInterpolatesGet()
	{
		$config = new LoggerConfig();

		$logger = new Logger($config);

		$_GET = ['bar' => 'baz'];
		$expected = 'DEBUG - '.date('Y-m-d').' --> Test message $_GET: '. print_r($_GET, true);

		$logger->log('debug', 'Test message {get_vars}');

		$logs = TestHandler::getLogs();

		$this->assertEquals(1, count($logs));
		$this->assertEquals($expected, $logs[0]);
	}

	//--------------------------------------------------------------------

	public function testLogInterpolatesSession()
	{
		$config = new LoggerConfig();

		$logger = new Logger($config);

		$_SESSION = ['xxx' => 'yyy'];
		$expected = 'DEBUG - '.date('Y-m-d').' --> Test message $_SESSION: '. print_r($_SESSION, true);

		$logger->log('debug', 'Test message {session_vars}');

		$logs = TestHandler::getLogs();

		$this->assertEquals(1, count($logs));
		$this->assertEquals($expected, $logs[0]);
	}

	//--------------------------------------------------------------------

	public function testLogInterpolatesCurrentEnvironment()
	{
		$config = new LoggerConfig();

		$logger = new Logger($config);

		$expected = 'DEBUG - '.date('Y-m-d').' --> Test message '. ENVIRONMENT;

		$logger->log('debug', 'Test message {env}');

		$logs = TestHandler::getLogs();

		$this->assertEquals(1, count($logs));
		$this->assertEquals($expected, $logs[0]);
	}

	//--------------------------------------------------------------------

	public function testLogInterpolatesEnvironmentVars()
	{
		$config = new LoggerConfig();

		$logger = new Logger($config);

		$_ENV['foo'] = 'bar';

		$expected = 'DEBUG - '.date('Y-m-d').' --> Test message bar';

		$logger->log('debug', 'Test message {env:foo}');

		$logs = TestHandler::getLogs();

		$this->assertEquals(1, count($logs));
		$this->assertEquals($expected, $logs[0]);
	}

	//--------------------------------------------------------------------

	public function testLogInterpolatesFileAndLine()
	{
		$config = new LoggerConfig();

		$logger = new Logger($config);

		$_ENV['foo'] = 'bar';

		// For whatever reason, this will often be the class/function instead of file and line.
		$expected = 'DEBUG - '.date('Y-m-d').' --> Test message CodeIgniter\Log\LoggerTest testLogInterpolatesFileAndLine';

		$logger->log('debug', 'Test message {file} {line}');

		$logs = TestHandler::getLogs();

		$this->assertEquals(1, count($logs));
		$this->assertEquals($expected, $logs[0]);
	}

	//--------------------------------------------------------------------

	public function testEmergencyLogsCorrectly()
	{
		$config = new LoggerConfig();
		$logger = new Logger($config);

		$expected = 'EMERGENCY - '.date('Y-m-d').' --> Test message';

		$logger->emergency('Test message');

		$logs = TestHandler::getLogs();

		$this->assertEquals(1, count($logs));
		$this->assertEquals($expected, $logs[0]);
	}

	//--------------------------------------------------------------------

	public function testAlertLogsCorrectly()
	{
		$config = new LoggerConfig();
		$logger = new Logger($config);

		$expected = 'ALERT - '.date('Y-m-d').' --> Test message';

		$logger->alert('Test message');

		$logs = TestHandler::getLogs();

		$this->assertEquals(1, count($logs));
		$this->assertEquals($expected, $logs[0]);
	}

	//--------------------------------------------------------------------

	public function testCriticalLogsCorrectly()
	{
		$config = new LoggerConfig();
		$logger = new Logger($config);

		$expected = 'CRITICAL - '.date('Y-m-d').' --> Test message';

		$logger->critical('Test message');

		$logs = TestHandler::getLogs();

		$this->assertEquals(1, count($logs));
		$this->assertEquals($expected, $logs[0]);
	}

	//--------------------------------------------------------------------

	public function testErrorLogsCorrectly()
	{
		$config = new LoggerConfig();
		$logger = new Logger($config);

		$expected = 'ERROR - '.date('Y-m-d').' --> Test message';

		$logger->error('Test message');

		$logs = TestHandler::getLogs();

		$this->assertEquals(1, count($logs));
		$this->assertEquals($expected, $logs[0]);
	}

	//--------------------------------------------------------------------

	public function testWarningLogsCorrectly()
	{
		$config = new LoggerConfig();
		$logger = new Logger($config);

		$expected = 'WARNING - '.date('Y-m-d').' --> Test message';

		$logger->warning('Test message');

		$logs = TestHandler::getLogs();

		$this->assertEquals(1, count($logs));
		$this->assertEquals($expected, $logs[0]);
	}

	//--------------------------------------------------------------------

	public function testNoticeLogsCorrectly()
	{
		$config = new LoggerConfig();
		$logger = new Logger($config);

		$expected = 'NOTICE - '.date('Y-m-d').' --> Test message';

		$logger->notice('Test message');

		$logs = TestHandler::getLogs();

		$this->assertEquals(1, count($logs));
		$this->assertEquals($expected, $logs[0]);
	}

	//--------------------------------------------------------------------

	public function testInfoLogsCorrectly()
	{
		$config = new LoggerConfig();
		$logger = new Logger($config);

		$expected = 'INFO - '.date('Y-m-d').' --> Test message';

		$logger->info('Test message');

		$logs = TestHandler::getLogs();

		$this->assertEquals(1, count($logs));
		$this->assertEquals($expected, $logs[0]);
	}

	//--------------------------------------------------------------------

	public function testDebugLogsCorrectly()
	{
		$config = new LoggerConfig();
		$logger = new Logger($config);

		$expected = 'DEBUG - '.date('Y-m-d').' --> Test message';

		$logger->debug('Test message');

		$logs = TestHandler::getLogs();

		$this->assertEquals(1, count($logs));
		$this->assertEquals($expected, $logs[0]);
	}

	//--------------------------------------------------------------------

}
