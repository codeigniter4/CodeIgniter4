<?php namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

class SessionsCommandsTest extends CIUnitTestCase
{
	private $streamFilter;

	protected function setUp(): void
	{
		parent::setUp();

		CITestStreamFilter::$buffer = '';
		$this->streamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
		$this->streamFilter         = stream_filter_append(STDERR, 'CITestStreamFilter');
	}

	public function tearDown(): void
	{
		stream_filter_remove($this->streamFilter);

		$result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', CITestStreamFilter::$buffer);
		$file   = trim(substr($result, 14));
		$file   = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, $file);
		file_exists($file) && unlink($file);
	}

	public function testCreateMigrationCommand()
	{
		command('session:migration');
		$result = CITestStreamFilter::$buffer;

		// make sure we end up with a migration class in the right place
		// or at least that we claim to have done so
		// separate assertions avoid console color codes
		$this->assertStringContainsString('Created file:', $result);
		$this->assertStringContainsString('_CreateCiSessionsTable.php', $result);
	}

	public function testOverriddenCreateMigrationCommand()
	{
		command('session:migration -t mygoodies');
		$result = CITestStreamFilter::$buffer;

		// make sure we end up with a migration class in the right place
		$this->assertStringContainsString('Created file:', $result);
		$this->assertStringContainsString('_CreateMygoodiesTable.php', $result);
	}

	public function testCannotWriteFileOnCreateMigrationCommand()
	{
		if ('\\' === DIRECTORY_SEPARATOR)
		{
			$this->markTestSkipped('chmod does not work as expected on Windows');
		}

		chmod(APPPATH . 'Database/Migrations', 0444);

		command('session:migration');
		$this->assertStringContainsString('Error in creating file:', CITestStreamFilter::$buffer);

		chmod(APPPATH . 'Database/Migrations', 0755);
	}

}
