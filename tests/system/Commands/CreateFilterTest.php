<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

class CreateFilterTest extends CIUnitTestCase
{
	protected $streamFilter;

	protected function setUp(): void
	{
		CITestStreamFilter::$buffer = '';
		$this->streamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
		$this->streamFilter         = stream_filter_append(STDERR, 'CITestStreamFilter');
	}

	protected function tearDown(): void
	{
		stream_filter_remove($this->streamFilter);

		$result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', CITestStreamFilter::$buffer);
		$file   = trim(substr($result, 14));
		$file   = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, $file);
		file_exists($file) && unlink($file);
	}

	protected function getBuffer(): string
	{
		return CITestStreamFilter::$buffer;
	}

	public function testCreateFilterWorks()
	{
		command('make:filter admin');

		$this->assertStringContainsString('Created file: ', $this->getBuffer());
		$this->assertFileExists(APPPATH . 'Filters/Admin.php');
	}
}
