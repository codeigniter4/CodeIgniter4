<?php

namespace CodeIgniter\Log;

use CodeIgniter\Log\Exceptions\LogException;
use Tests\Support\Log\Config\MockLoggerConfig;
use CodeIgniter\Log\Handlers\FileHandler;
use org\bovigo\vfs\vfsStream;

class FileHandlerTest extends\CodeIgniter\Test\CIUnitTestCase
{
	protected $config;

	protected function setUp(): void
	{
		parent::setUp();
		vfsStream::setup('logs');
		$this->config          = new MockLoggerConfig();
		$this->config->logsDir = vfsStream::url('logs/');
	}

	public function testCanHandle()
	{
		$this->config->fileLevelsHandled = 3;

		$handler = new FileHandler($this->config);
		$this->assertTrue($handler->canHandle('alert'));
	}

	public function testCannotHandle()
	{
		$this->config->fileLevelsHandled = 3;

		$handler = new FileHandler($this->config);
		$this->assertFalse($handler->canHandle('debug'));
	}

	public function testSetLevelsHandledZeroInArray()
	{
		$this->config->fileLevelsHandled = 3;

		$handler = new FileHandler($this->config);
		$handler->setLevelsHandled([0]);
		$this->assertFalse($handler->canHandle('debug'));
	}

	public function testGetLevelsHandled()
	{
		$this->config->fileLevelsHandled = [3];

		$handler = new FileHandler($this->config);
		$this->assertSame($handler->getLevelsHandled(), ['critical']);
	}

	public function testCannotHandleWithEmptyLevelsHandled()
	{
		$this->config->fileLevelsHandled = [];

		$handler = new FileHandler($this->config);
		$this->assertFalse($handler->canHandle('debug'));
	}

	public function testBasicHandle()
	{
		$this->config->fileLevelsHandled = 5;

		$handler = new FileHandler($this->config);
		$this->assertTrue($handler->handle('warning', 'This is a test log'));
	}


	public function testHandleCreateFile()
	{
		$this->config->fileLevelsHandled = 5;

		$handler = new FileHandler($this->config);

		$expected = 'Only the good die young';
		$handler->handle('warning', $expected);

		$file     = 'CI_' . date('Y-m-d') . '.log';
		$contents = file_get_contents($this->config->logsDir . $file);

		// did the log file get created?
		$this->assertStringContainsString($expected, $contents);
	}

	public function testHandleNumericLevel()
	{
		$this->config->fileLevelsHandled = 5;

		$handler = new FileHandler($this->config);

		$expected = 'Only the good die young';
		$handler->handle(5, $expected);

		$file     = 'CI_' . date('Y-m-d') . '.log';
		$contents = file_get_contents($this->config->logsDir . $file);

		// did the log file get created?
		$this->assertStringContainsString($expected, $contents);
	}

	public function testMakeWithPhpExt()
	{
		$this->config->fileLevelsHandled = 5;
		$this->config->fileExtension = 'php';

		$handler = new FileHandler($this->config);
		$handler->handle('debug', 'Test message');

		$success = file_exists($this->config->logsDir . 'CI_' . date('Y-m-d') . '.php');
		$this->assertTrue($success);
	}

	public function testHandleDateTimeCorrectly()
	{
		$this->config->fileLevelsHandled = 5;
		$this->config->dateFormat    = 'Y-m-d H:i:s:u';

		$handler = new FileHandler($this->config);
		$handler->handle('debug', 'Test message');

		$file     = 'CI_' . date('Y-m-d') . '.log';
		$contents = file_get_contents($this->config->logsDir . $file);

		// three colons (:) with microsecond time format
		$this->assertEquals(3, substr_count($contents, ':'));
	}

	public function testSetFileName()
	{
		$this->config->fileLevelsHandled = [8];

		$handler = new FileHandler($this->config);
		$handler->setFileName('Testing_');
		$handler->handle('debug', 'Test message');

		$this->assertTrue(file_exists($this->config->logsDir . 'Testing_' . date('Y-m-d') . '.log'));
	}

	public function testSetFileExtension()
	{
		$this->config->fileLevelsHandled = [8];
		$handler                     = new FileHandler($this->config);
		$handler->setFileExtension('.txt++');

		$handler->handle('debug', 'Test message');
		$this->assertTrue(file_exists($this->config->logsDir . 'CI_' . date('Y-m-d') . '.txt'));
	}

	public function testSetFileNameInvalidNameException()
	{
		$this->config->fileLevelsHandled = [8];

		$handler = new FileHandler($this->config);
		$this->expectException(LogException::class);
		$handler->setFileName('<script>');
	}

	public function testSetDateFormat()
	{
		$this->config->fileLevelsHandled = 5;

		$handler = new FileHandler($this->config);
		$handler->setDateFormat('Y-m-d H:i:s:u');

		$handler->handle('debug', 'Test message');

		$file     = 'CI_' . date('Y-m-d') . '.log';
		$contents = file_get_contents($this->config->logsDir . $file);

		// three colons (:) with microsecond time format
		$this->assertEquals(3, substr_count($contents, ':'));
	}

	public function testExceptionOnUnsetLevelsHandled()
	{
		$this->config->fileLevelsHandled = null;
		$this->expectException(LogException::class);
		new FileHandler($this->config);
	}

	public function testExceptionOnNonNumericLevel()
	{
		$this->config->fileLevelsHandled = 'x';
		$this->expectException(\InvalidArgumentException::class);
		new FileHandler($this->config);
	}

	/**
	 * @dataProvider outOfBoundsProvider
	 */
	public function testExceptionOnLevelOutofBounds($level)
	{
		$this->config->fileLevelsHandled = $level;
		$this->expectException(\InvalidArgumentException::class);
		new FileHandler($this->config);
	}

	public function outOfBoundsProvider()
	{
		return [
			[10],
			[-1],
		];
	}

	public function testExceptionOnNonNumericLevelinArray()
	{
		$this->config->fileLevelsHandled = [
			3,
			'x',
			7,
		];
		$this->expectException(\InvalidArgumentException::class);
		new FileHandler($this->config);
	}

	public function testCanHandleExceptionOnBadInput()
	{
		$this->config->fileLevelsHandled = [3];

		$handler = new FileHandler($this->config);

		$this->expectException(\InvalidArgumentException::class);
		$handler->canHandle('bogus');
	}

	/**
	 * @dataProvider nonSequentialProvider
	 */
	public function testCanHandleNonSequentialLevels($level, $expected)
	{
		$this->config->fileLevelsHandled = [
			1,
			3,
			5,
		]; //emergency, critical, warning

		$handler = new FileHandler($this->config);

		$this->assertSame($expected, $handler->canHandle($level));
	}

	public function nonSequentialProvider()
	{
		return[
			[
				'emergency',
				true,
			],
			[
				'critical',
				true,
			],
			[
				'warning',
				true,
			],
			[
				'alert',
				false,
			],
			[
				'info',
				false,
			],
		];
	}

}
