<?php

namespace CodeIgniter\Commands;

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
		$file = WRITEPATH . 'user.db';

		if (file_exists($file))
		{
			unlink($file);
		}

		parent::tearDown();
	}

	protected function getBuffer()
	{
		return CITestStreamFilter::$buffer;
	}

	public function testCreateDatabase()
	{
		command('db:create user');
		$this->assertStringContainsString('successfully created.', $this->getBuffer());
	}

	public function testSqliteDatabaseDuplicated()
	{
		if (Database::connect()->getPlatform() !== 'SQLite3')
		{
			$this->markTestSkipped('Needs to run on SQLite3.');
		}

		command('db:create user');
		CITestStreamFilter::$buffer = '';

		command('db:create user --ext db');
		$this->assertStringContainsString('already exists.', $this->getBuffer());
	}
}
