<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

class ConfigurableSortImportsTest extends CIUnitTestCase
{
	protected $streamFilter;

	protected function setUp(): void
	{
		parent::setUp();

		CITestStreamFilter::$buffer = '';
		$this->streamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
		$this->streamFilter         = stream_filter_append(STDERR, 'CITestStreamFilter');
	}

	protected function tearDown(): void
	{
		parent::tearDown();
		stream_filter_remove($this->streamFilter);

		$result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', CITestStreamFilter::$buffer);
		$file   = trim(substr($result, 14));
		$file   = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, $file);
		$dir    = dirname($file);
		file_exists($file) && unlink($file);
		is_dir($dir) && rmdir($dir);
	}

	public function testEnabledSortImportsWillDisruptLanguageFilePublish()
	{
		command('publish:language --lang es');

		$file = APPPATH . 'Language/es/Foobar.php';
		$this->assertStringContainsString('Created file: ', CITestStreamFilter::$buffer);
		$this->assertFileExists($file);
		$this->assertNotSame(sha1_file(SUPPORTPATH . 'Commands/Foobar.php'), sha1_file($file));
	}

	public function testDisabledSortImportsWillNotAffectLanguageFilesPublish()
	{
		command('publish:language --lang es --sort off');

		$file = APPPATH . 'Language/es/Foobar.php';
		$this->assertStringContainsString('Created file: ', CITestStreamFilter::$buffer);
		$this->assertFileExists($file);
		$this->assertSame(sha1_file(SUPPORTPATH . 'Commands/Foobar.php'), sha1_file($file));
	}
}
