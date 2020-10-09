<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Config\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use Config\Database;

class CreateDatabaseTest extends CIUnitTestCase
{
	protected $streamFilter;

	protected function setUp(): void
	{
		CITestStreamFilter::$buffer = '';

		$this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
		$this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');

		parent::setUp();
	}

	protected function tearDown(): void
	{
		stream_filter_remove($this->streamFilter);

		if (Database::connect()->getPlatform() === 'SQLite3')
		{
			$file = WRITEPATH . 'foobar.db';

			if (file_exists($file))
			{
				unlink($file);
			}
		}
		else
		{
			Database::forge()->dropDatabase('foobar');
		}

		Config::reset();
		parent::tearDown();
	}

	protected function getBuffer()
	{
		return CITestStreamFilter::$buffer;
	}

	public function testCreateDatabase()
	{
		command('db:create foobar');
		$this->assertStringContainsString('successfully created.', $this->getBuffer());
	}

	public function testSqliteDatabaseDuplicated()
	{
		if (Database::connect()->getPlatform() !== 'SQLite3')
		{
			$this->markTestSkipped('Needs to run on SQLite3.');
		}

		command('db:create foobar');
		CITestStreamFilter::$buffer = '';

		command('db:create foobar --ext db');
		$this->assertStringContainsString('already exists.', $this->getBuffer());
	}

	public function testOtherDriverDuplicatedDatabaseThrowsDatabaseException()
	{
		if (Database::connect()->getPlatform() === 'SQLite3')
		{
			$this->markTestSkipped('Needs to run on non-SQLite3 drivers.');
		}

		command('db:create foobar');
		CITestStreamFilter::$buffer = '';

		command('db:create foobar');
		$this->assertStringContainsString('Unable to create the specified database.', $this->getBuffer());
	}

	public function testOtherDriverDuplicatedDatabaseOnSilent()
	{
		if (Database::connect()->getPlatform() === 'SQLite3')
		{
			$this->markTestSkipped('Needs to run on non-SQLite3 drivers.');
		}

		config('Database')->tests['DBDebug'] = false;

		command('db:create foobar');
		CITestStreamFilter::$buffer = '';

		command('db:create foobar');
		$this->assertStringContainsString('Database creation failed.', $this->getBuffer());
	}
}
