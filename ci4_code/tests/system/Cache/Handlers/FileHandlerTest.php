<?php

namespace CodeIgniter\Cache\Handlers;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Cache;

class FileHandlerTest extends CIUnitTestCase
{

	private static $directory = 'FileHandler';
	private static $key1      = 'key1';
	private static $key2      = 'key2';
	private static $key3      = 'key3';

	private static function getKeyArray()
	{
		return [
			self::$key1,
			self::$key2,
			self::$key3,
		];
	}

	private static $dummy = 'dymmy';
	private $fileHandler;
	private $config;

	protected function setUp(): void
	{
		parent::setUp();

		if (! function_exists('octal_permissions'))
		{
			helper('filesystem');
		}

		// Initialize path
		$this->config                     = new Cache();
		$this->config->file['storePath'] .= self::$directory;

		if (! is_dir($this->config->file['storePath']))
		{
			mkdir($this->config->file['storePath'], 0777, true);
		}

		$this->fileHandler = new FileHandler($this->config);
		$this->fileHandler->initialize();
	}

	public function tearDown(): void
	{
		if (is_dir($this->config->file['storePath']))
		{
			chmod($this->config->file['storePath'], 0777);

			foreach (self::getKeyArray() as $key)
			{
				if (is_file($this->config->file['storePath'] . DIRECTORY_SEPARATOR . $key))
				{
					chmod($this->config->file['storePath'] . DIRECTORY_SEPARATOR . $key, 0777);
					unlink($this->config->file['storePath'] . DIRECTORY_SEPARATOR . $key);
				}
			}

			rmdir($this->config->file['storePath']);
		}
	}

	public function testNew()
	{
		$this->assertInstanceOf(FileHandler::class, $this->fileHandler);
	}

	public function testNewWithNonWritablePath()
	{
		$this->expectException('CodeIgniter\Cache\Exceptions\CacheException');

		chmod($this->config->file['storePath'], 0444);
		new FileHandler($this->config);
	}

	public function testSetDefaultPath()
	{
		// Initialize path
		$config                    = new Cache();
		$config->file['storePath'] = null;

		$this->fileHandler = new FileHandler($config);
		$this->fileHandler->initialize();

		$this->assertInstanceOf(FileHandler::class, $this->fileHandler);
	}

	public function testGet()
	{
		$this->fileHandler->save(self::$key1, 'value', 2);

		$this->assertSame('value', $this->fileHandler->get(self::$key1));
		$this->assertNull($this->fileHandler->get(self::$dummy));

		CLI::wait(3);
		$this->assertNull($this->fileHandler->get(self::$key1));
	}

	public function testRemember()
	{
		$this->fileHandler->remember(self::$key1, 2, function () {
			return 'value';
		});

		$this->assertSame('value', $this->fileHandler->get(self::$key1));
		$this->assertNull($this->fileHandler->get(self::$dummy));

		CLI::wait(3);
		$this->assertNull($this->fileHandler->get(self::$key1));
	}

	public function testSave()
	{
		$this->assertTrue($this->fileHandler->save(self::$key1, 'value'));

		chmod($this->config->file['storePath'], 0444);
		$this->assertFalse($this->fileHandler->save(self::$key2, 'value'));
	}

	public function testDelete()
	{
		$this->fileHandler->save(self::$key1, 'value');

		$this->assertTrue($this->fileHandler->delete(self::$key1));
		$this->assertFalse($this->fileHandler->delete(self::$dummy));
	}

	public function testIncrement()
	{
		$this->fileHandler->save(self::$key1, 1);
		$this->fileHandler->save(self::$key2, 'value');

		$this->assertSame(11, $this->fileHandler->increment(self::$key1, 10));
		$this->assertFalse($this->fileHandler->increment(self::$key2, 10));
		$this->assertSame(10, $this->fileHandler->increment(self::$key3, 10));
	}

	public function testDecrement()
	{
		$this->fileHandler->save(self::$key1, 10);
		$this->fileHandler->save(self::$key2, 'value');

		//  Line following commented out to force the cache to add a zero entry for key3
		//      $this->fileHandler->save(self::$key3, 0);

		$this->assertSame(9, $this->fileHandler->decrement(self::$key1, 1));
		$this->assertFalse($this->fileHandler->decrement(self::$key2, 1));
		$this->assertSame(-1, $this->fileHandler->decrement(self::$key3, 1));
	}

	public function testClean()
	{
		$this->fileHandler->save(self::$key1, 1);
		$this->fileHandler->save(self::$key2, 'value');

		$this->assertTrue($this->fileHandler->clean());

		$this->fileHandler->save(self::$key1, 1);
		$this->fileHandler->save(self::$key2, 'value');
	}

	public function testGetMetaData()
	{
		$time = time();
		$this->fileHandler->save(self::$key1, 'value');

		$this->assertFalse($this->fileHandler->getMetaData(self::$dummy));

		$actual = $this->fileHandler->getMetaData(self::$key1);
		$this->assertLessThanOrEqual(60, $actual['expire'] - $time);
		$this->assertLessThanOrEqual(1, $actual['mtime'] - $time);
		$this->assertSame('value', $actual['data']);
	}

	public function testGetCacheInfo()
	{
		$time = time();
		$this->fileHandler->save(self::$key1, 'value');

		$actual = $this->fileHandler->getCacheInfo();
		$this->assertArrayHasKey(self::$key1, $actual);
		$this->assertEquals(self::$key1, $actual[self::$key1]['name']);
		$this->assertArrayHasKey('server_path', $actual[self::$key1]);
	}

	public function testIsSupported()
	{
		$this->assertTrue($this->fileHandler->isSupported());
	}

	/**
	 * @dataProvider modeProvider
	 */
	public function testSaveMode($int, $string)
	{
		// Initialize mode
		$config               = new Cache();
		$config->file['mode'] = $int;

		$this->fileHandler = new FileHandler($config);
		$this->fileHandler->initialize();

		$this->fileHandler->save(self::$key1, 'value');

		$file = $config->file['storePath'] . DIRECTORY_SEPARATOR . self::$key1;
		$mode = octal_permissions(fileperms($file));

		$this->assertEquals($string, $mode);
	}

	public function modeProvider()
	{
		return [
			[
				0640,
				'640',
			],
			[
				0600,
				'600',
			],
			[
				0660,
				'660',
			],
			[
				0777,
				'777',
			],
		];
	}

	//--------------------------------------------------------------------

	public function testFileHandler()
	{
		$fileHandler = new BaseTestFileHandler();

		$actual = $fileHandler->getFileInfoTest();

		$this->assertArrayHasKey('server_path', $actual);
		$this->assertArrayHasKey('size', $actual);
		$this->assertArrayHasKey('date', $actual);
		$this->assertArrayHasKey('readable', $actual);
		$this->assertArrayHasKey('writable', $actual);
		$this->assertArrayHasKey('executable', $actual);
		$this->assertArrayHasKey('fileperms', $actual);
	}

}

final class BaseTestFileHandler extends FileHandler
{

	private static $directory = 'FileHandler';
	private $config;

	public function __construct()
	{
		$this->config                     = new Cache();
		$this->config->file['storePath'] .= self::$directory;

		parent::__construct($this->config);
	}

	public function getFileInfoTest()
	{
		$tmpHandle = tmpfile();
		stream_get_meta_data($tmpHandle)['uri'];

		return $this->getFileInfo(stream_get_meta_data($tmpHandle)['uri'], [
			'name',
			'server_path',
			'size',
			'date',
			'readable',
			'writable',
			'executable',
			'fileperms',
		]);
	}

}
