<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

class CreateMigrationTest extends CIUnitTestCase
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

	public function testCreateMigration()
	{
		command('migrate:create databaseMigrator');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());
		$this->assertStringContainsString('_DatabaseMigrator.php', $this->getBuffer());
	}

	public function testCreateMigrationFailsOnUnwritableDirectory()
	{
		if ('\\' === DIRECTORY_SEPARATOR)
		{
			$this->markTestSkipped('chmod does not work as expected on Windows');
		}

		chmod(APPPATH . 'Database/Migrations', 0444);

		command('migrate:create migrateOne');
		$this->assertStringContainsString('Error in creating file: ', $this->getBuffer());

		chmod(APPPATH . 'Database/Migrations', 0755);
	}
}
