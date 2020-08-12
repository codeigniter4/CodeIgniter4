<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

class CreateScaffoldTest extends CIUnitTestCase
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

	public function testCreateScaffoldProducesManyFiles()
	{
		command('make:scaffold people');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());

		$paths = [
			'Controllers',
			'Models',
			'Entities',
		];

		foreach ($paths as $path)
		{
			$this->assertFileExists(APPPATH . $path . '/People.php');
		}
		$this->assertFileExists(APPPATH . 'Database/Migrations/' . gmdate(config('Migrations')->timestampFormat) . 'People.php');
		$this->assertFileExists(APPPATH . 'Database/Seeds/People.php');

		// cleanup
		foreach ($paths as $path)
		{
			unlink(APPPATH . $path . '/People.php');
		}
		unlink(APPPATH . 'Database/Migrations/' . gmdate(config('Migrations')->timestampFormat) . 'People.php');
		unlink(APPPATH . 'Database/Seeds/People.php');
		rmdir(APPPATH . 'Entities');
	}

	public function testCreateScaffoldCanPassManyOptionsToCommands()
	{
		command('make:scaffold user -bare');
		command('make:scaffold user -restful -dbgroup testing -force -table utilisateur');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());

		// File existence check
		$paths = [
			'Controllers',
			'Models',
			'Entities',
		];
		foreach ($paths as $path)
		{
			$this->assertFileExists(APPPATH . $path . '/User.php');
		}
		$this->assertFileExists(APPPATH . 'Database/Migrations/' . gmdate(config('Migrations')->timestampFormat) . 'User.php');
		$this->assertFileExists(APPPATH . 'Database/Seeds/User.php');

		// Options check
		$this->assertStringContainsString('extends ResourceController', $this->getFileContents(APPPATH . 'Controllers/User.php'));
		$this->assertStringContainsString('protected $table      = \'utilisateurs\';', $this->getFileContents(APPPATH . 'Models/User.php'));
		$this->assertStringContainsString('protected $DBGroup  = \'testing\';', $this->getFileContents(APPPATH . 'Models/User.php'));

		// cleanup
		foreach ($paths as $path)
		{
			unlink(APPPATH . $path . '/User.php');
		}
		unlink(APPPATH . 'Database/Migrations/' . gmdate(config('Migrations')->timestampFormat) . 'User.php');
		unlink(APPPATH . 'Database/Seeds/User.php');
		rmdir(APPPATH . 'Entities');
	}
}
