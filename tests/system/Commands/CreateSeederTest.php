<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

class CreateSeederTest extends CIUnitTestCase
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

	public function testCreateSeederCommand()
	{
		command('make:seeder testSeeder');

		$ds = DIRECTORY_SEPARATOR;
		$this->assertStringContainsString('Created file: ', $this->getBuffer());
		$this->assertStringContainsString("APPPATH{$ds}Database{$ds}Seeds{$ds}TestSeeder.php", $this->getBuffer());
	}

	public function testCreateSeederFailsOnDuplicateFile()
	{
		command('make:seeder seedOne');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());

		command('make:seeder seedOne');
		$this->assertStringContainsString('SeedOne.php already exists.', $this->getBuffer());

		command('make:seeder seedOne -force');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());
	}
}
