<?php
namespace CodeIgniter\Commands;

use Config\Services;
use CodeIgniter\CLI\CommandRunner;

class BaseCommandTest extends \CodeIgniter\Test\CIUnitTestCase
{
	protected $logger;
	protected $runner;

	protected function setUp(): void
	{
		parent::setUp();
		$this->logger   = Services::logger();
		$this->runner   = new CommandRunner();
	}

	public function testMagicIssetTrue()
	{
		$command = new \Tests\Support\Commands\AppInfo($this->logger, $this->runner);

		$this->assertTrue(isset($command->group));
	}

	public function testMagicIssetFalse()
	{
		$command = new \Tests\Support\Commands\AppInfo($this->logger, $this->runner);

		$this->assertFalse(isset($command->foobar));
	}

	public function testMagicGet()
	{
		$command = new \Tests\Support\Commands\AppInfo($this->logger, $this->runner);

		$this->assertEquals('demo', $command->group);
	}

	public function testMagicGetMissing()
	{
		$command = new \Tests\Support\Commands\AppInfo($this->logger, $this->runner);

		$this->assertNull($command->foobar);
	}
}
