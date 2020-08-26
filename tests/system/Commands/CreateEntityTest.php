<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

class CreateEntityTest extends CIUnitTestCase
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
	}

	protected function getBuffer(): string
	{
		return CITestStreamFilter::$buffer;
	}

	protected function getFileContents(string $filepath): string
	{
		if (! file_exists($filepath))
		{
			return '';
		}

		$contents = file_get_contents($filepath);

		return $contents ?: '';
	}

	public function testCreateEntityCreatesASkeletonEntityFile()
	{
		command('make:entity user');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());
		$this->assertStringContainsString('User.php', $this->getBuffer());
		$this->assertFileExists(APPPATH . 'Entities/User.php');
		$this->assertStringContainsString('use CodeIgniter\\Entity;', $this->getFileContents(APPPATH . 'Entities/User.php'));

		// cleanup
		unlink(APPPATH . 'Entities/User.php');
		rmdir(APPPATH . 'Entities');
	}

	public function testCreateEntityCreatesInOtherNamespace()
	{
		command('make:entity user -n CodeIgniter');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());
		$this->assertStringContainsString('User.php', $this->getBuffer());
		$this->assertFileExists(SYSTEMPATH . 'Entities/User.php');
		$this->assertStringContainsString('use CodeIgniter\\Entity;', $this->getFileContents(SYSTEMPATH . 'Entities/User.php'));

		// cleanup
		unlink(SYSTEMPATH . 'Entities/User.php');
		rmdir(SYSTEMPATH . 'Entities');
	}
}
