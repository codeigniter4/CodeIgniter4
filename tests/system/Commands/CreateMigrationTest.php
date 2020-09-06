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

		$result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', CITestStreamFilter::$buffer);
		$file   = trim(substr($result, 14));
		$file   = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, $file);
		file_exists($file) && unlink($file);
	}

	protected function getBuffer(): string
	{
		return CITestStreamFilter::$buffer;
	}

	public function testCreateMigration()
	{
		command('make:migration databaseMigrator');
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

		command('make:migration migrateOne');
		$this->assertStringContainsString('Error in creating file: ', $this->getBuffer());

		chmod(APPPATH . 'Database/Migrations', 0755);
	}

	public function testCreateMigrationFailOnUndefinedNamespace()
	{
		try
		{
			command('make:migration migrateTwo -n CodeIgnite');
		}
		catch (\Throwable $e)
		{
			ob_end_clean();
			$this->assertInstanceOf('RuntimeException', $e);
			$this->assertEquals('Namespace "CodeIgnite" is not defined.', $e->getMessage());
		}
	}

	public function testCreateMigrationOnOtherNamespace()
	{
		command('make:migration migrateThree -n CodeIgniter');
		$this->assertStringContainsString('Created file:', $this->getBuffer());
		$this->assertStringContainsString('SYSTEMPATH', $this->getBuffer());

		// cleanup
		$result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', CITestStreamFilter::$buffer);
		$file   = trim(substr($result, 14));
		$file   = str_replace('SYSTEMPATH' . DIRECTORY_SEPARATOR, SYSTEMPATH, $file);
		$dir    = dirname($file);
		file_exists($file) && unlink($file);
		rmdir($dir);
	}
}
