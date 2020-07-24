<?php
namespace CodeIgniter\Commands;

use CodeIgniter\CLI\CommandRunner;
use Config\Services;

class BaseCommandTest extends \CodeIgniter\Test\CIUnitTestCase
{
	protected $logger;
	protected $runner;

	protected function setUp(): void
	{
		parent::setUp();
		$this->logger = Services::logger();
		$this->runner = new CommandRunner();
	}

	public function testMagicIssetTrue()
	{
		$command = new \Tests\Support\Commands\AppInfo($this->logger, service('commands'));

		$this->assertTrue(isset($command->group));
	}

	public function testMagicIssetFalse()
	{
		$command = new \Tests\Support\Commands\AppInfo($this->logger, service('commands'));

		$this->assertFalse(isset($command->foobar));
	}

	public function testMagicGet()
	{
		$command = new \Tests\Support\Commands\AppInfo($this->logger, service('commands'));

		$this->assertEquals('demo', $command->group);
	}

	public function testMagicGetMissing()
	{
		$command = new \Tests\Support\Commands\AppInfo($this->logger, service('commands'));

		$this->assertNull($command->foobar);
	}
}
