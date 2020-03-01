<?php

namespace CodeIgniter\Log;

use CodeIgniter\Log\Logger;
use Tests\Support\Log\Config\MockLoggerConfig;
use CodeIgniter\Log\Exceptions\LogException;
use org\bovigo\vfs\vfsStream;

use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

class LoggerTest extends \CodeIgniter\Test\CIUnitTestCase
{

    protected $config;
    protected $logger;
    protected $fileName;
    protected $root;

    protected function setUp(): void
    {
	parent::setUp();

	$this->config = new MockLoggerConfig();
	$this->logger = new Logger($this->config);

	$this->fileName = 'CI_' . date('Y-m-d') . '.log';

	vfsStream::setup('logs');
	$this->root = vfsStream::url('logs/');

	$fileHandler = $this->logger->getHandlers('FileHandler');
	$fileHandler->setPath($this->root);
	$fileHandler->setLevelsHandled(8);
    }

    public function testLoggerThrowsExceptionWhenConfigMissingHandlers()
    {
	unset($this->config->handlers);

	$this->expectException(LogException::class);
	$logger = new Logger($this->config);
    }

    public function testGetLoggerHandlers()
    {
	$handlers = $this->logger->getHandlers();
	$fileHandler = $handlers['FileHandler'];
	$this->assertEquals('\CodeIgniter\Log\Handlers\FileHandler', '\\' . get_class($fileHandler));
    }

    public function testLogActuallyMakesFile()
    {
	$before = file_exists($this->root . $this->fileName); //false
	$this->logger->log('debug', 'Test message'); //write file
	$after = file_exists($this->root . $this->fileName); // true - we hope

	$this->assertNotEquals($before, $after);
    }

    /**
     * @group testme
     */
    public function testNoFileIWhenUnhandledLevel()
    {
	$handlers = $this->logger->getHandlers();
	$fileHandler = $handlers['FileHandler'];
	$fileHandler->setLevelsHandled(1);

	$before = file_exists($this->root . $this->fileName); //false
	$this->logger->log('debug', 'Test message'); //write file
	$after = file_exists($this->root . $this->fileName); // false - we hope
	$this->assertEquals($before, $after);
    }

    public function levelsProvider()
    {
	return[
		[
			'emergency'],
		[
			'alert'],
		[
			'critical'],
		[
			'error'],
		[
			'warning'],
		[
			'notice'],
		[
			'info'],
		[
			'debug'],
	];
    }

    /**
     * @dataProvider levelsProvider
     */
    public function testAllLevelSpecificMethods($level)
    {
	//write file
	$this->logger->$level('{level} Test Message', [
		'level' => $level]);

	$contents = file_get_contents($this->root . $this->fileName);
	$this->assertStringContainsStringIgnoringCase($level, $contents);
    }

    public function testLoggerInterpolatesFileLineAndContext()
    {
	$msg = 'Hey {name}, {file} blew up on line {line}!';
	$context = [
		'name' => 'bubba'];
	$x = __LINE__ + 1;
	$this->logger->error($msg, $context);

	$contents = file_get_contents($this->root . $this->fileName);

	$this->assertStringContainsStringIgnoringCase('bubba', $contents,
		'bubba not found');
	$this->assertStringContainsString('LoggerTest.php', $contents,
		'file name not found');
	$this->assertStringContainsString("line $x", $contents, 'line number not found');
    }

    public function testLogInterpolatesPost()
    {
	$_POST = [
		'foo' => 'bar'];

	$this->logger->error('Test message {post_vars}');

	$contents = file_get_contents($this->root . $this->fileName);
	$this->assertStringContainsString('[foo] => bar', $contents,
		'Could not find [foo] => bar');
    }

    public function testLogInterpolatesGet()
    {
	$_GET = [
		'bar' => 'baz'];
	$this->logger->emergency('Test message {get_vars}');

	$contents = file_get_contents($this->root . $this->fileName);
	$this->assertStringContainsString('[bar] => baz', $contents,
		'Could not find [bar] => baz');
    }

    public function testLogInterpolatesSession()
    {
	$_SESSION = [
		'xxx' => 'yyy'];

	$this->logger->alert('Test message {session_vars}');
	$contents = file_get_contents($this->root . $this->fileName);

	$this->assertStringContainsString('[xxx] => yyy', $contents,
		'Could not find [xxx] => yyy');
    }

    public function testLogInterpolatesCurrentEnvironment()
    {
	$this->logger->critical('Test message {env}');
	$contents = file_get_contents($this->root . $this->fileName);
	$this->assertStringContainsString(ENVIRONMENT, $contents);
    }

    public function testLogInterpolatesEnvironmentVars()
    {
	$_ENV['foo'] = 'bar';
	$this->logger->error('Test message {env:foo}');

	$contents = file_get_contents($this->root . $this->fileName);
	$this->assertStringContainsString('Test message bar', $contents);
    }

    public function testLogInterpolatesExceptions()
    {
	$expected = '[ERROR] These are not the droids you are looking for';

	try
	{
	    throw new \Exception('These are not the droids you are looking for');
	}
	catch (\Exception $e)
	{
	    $this->logger->log('error', '[ERROR] {exception}', [
		    'exception' => $e]);
	}
	$contents = file_get_contents($this->root . $this->fileName);
	$this->assertStringContainsString($expected, $contents);
    }

    public function testLogLevels()
    {
	$this->logger->log(5, 'Test message');

	$contents = file_get_contents($this->root . $this->fileName);
	$this->assertStringContainsString('WARNING', $contents);
    }

    public function testNonStringMessage()
    {
	$this->logger->log(8, $this->config);

	$contents = file_get_contents($this->root . $this->fileName);
	$this->assertStringContainsString('[handlers] => Array', $contents);
    }

}
