<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\CLI;

use CodeIgniter\Log\Logger;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Services;

/**
 * @internal
 */
final class CommandRunnerTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private static Logger $logger;
    private static CommandRunner $runner;

    public static function setUpBeforeClass(): void
    {
        self::$logger = service('logger');
        self::$runner = new CommandRunner();

        self::$runner->initController(service('request'), service('response'), self::$logger);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerStreamFilterClass()
            ->appendOutputStreamFilter()
            ->appendErrorStreamFilter();
    }

    protected function tearDown(): void
    {
        $this->removeOutputStreamFilter()->removeErrorStreamFilter();
    }

    public function testGoodCommand()
    {
        self::$runner->index(['list']);

        // make sure the result looks like a command list
        $this->assertStringContainsString('Lists the available commands.', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Displays basic usage information.', $this->getStreamFilterBuffer());
    }

    public function testDefaultCommand()
    {
        self::$runner->index([]);

        // make sure the result looks like basic help
        $this->assertStringContainsString('Lists the available commands.', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Displays basic usage information.', $this->getStreamFilterBuffer());
    }

    public function testHelpCommand()
    {
        self::$runner->index(['help']);

        // make sure the result looks like basic help
        $this->assertStringContainsString('Displays basic usage information.', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('help command_name', $this->getStreamFilterBuffer());
    }

    public function testHelpCommandDetails()
    {
        self::$runner->index(['help', 'session:migration']);

        // make sure the result looks like more detailed help
        $this->assertStringContainsString('Description:', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Usage:', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Options:', $this->getStreamFilterBuffer());
    }

    public function testCommandProperties()
    {
        $commands = self::$runner->getCommands();
        $command  = new $commands['help']['class'](self::$logger, Services::commands());

        $this->assertSame('Displays basic usage information.', $command->description);
        $this->assertNull($command->notdescription);
    }

    public function testEmptyCommand()
    {
        self::$runner->index([null, 'list']);

        // make sure the result looks like a command list
        $this->assertStringContainsString('Lists the available commands.', $this->getStreamFilterBuffer());
    }

    public function testBadCommand()
    {
        self::$runner->index(['bogus']);

        // make sure the result looks like a command list
        $this->assertStringContainsString('Command "bogus" not found', $this->getStreamFilterBuffer());
    }
}
