<?php

namespace CodeIgniter\Commands;

use CodeIgniter\CLI\CommandRunner;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Tests\Support\Commands\AppInfo;

/**
 * @internal
 */
final class BaseCommandTest extends CIUnitTestCase
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
        $command = new AppInfo($this->logger, service('commands'));

        $this->assertTrue(isset($command->group));
    }

    public function testMagicIssetFalse()
    {
        $command = new AppInfo($this->logger, service('commands'));

        $this->assertFalse(isset($command->foobar));
    }

    public function testMagicGet()
    {
        $command = new AppInfo($this->logger, service('commands'));

        $this->assertSame('demo', $command->group);
    }

    public function testMagicGetMissing()
    {
        $command = new AppInfo($this->logger, service('commands'));

        $this->assertNull($command->foobar);
    }
}
