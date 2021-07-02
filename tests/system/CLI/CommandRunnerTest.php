<?php

namespace CodeIgniter\CLI;

use CodeIgniter\Log\Logger;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use Config\Services;

/**
 * @internal
 */
final class CommandRunnerTest extends CIUnitTestCase
{
    /**
     * @var resource
     */
    private $streamFilter;

    /**
     * @var Logger
     */
    private static $logger;

    /**
     * @var CommandRunner
     */
    private static $runner;

    public static function setUpBeforeClass(): void
    {
        self::$logger = service('logger');
        self::$runner = new CommandRunner();

        self::$runner->initController(service('request'), service('response'), self::$logger);
    }

    protected function setUp(): void
    {
        parent::setUp();

        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);
    }

    public function testGoodCommand()
    {
        self::$runner->index(['list']);
        $result = CITestStreamFilter::$buffer;

        // make sure the result looks like a command list
        $this->assertStringContainsString('Lists the available commands.', $result);
        $this->assertStringContainsString('Displays basic usage information.', $result);
    }

    public function testDefaultCommand()
    {
        self::$runner->index([]);
        $result = CITestStreamFilter::$buffer;

        // make sure the result looks like basic help
        $this->assertStringContainsString('Lists the available commands.', $result);
        $this->assertStringContainsString('Displays basic usage information.', $result);
    }

    public function testHelpCommand()
    {
        self::$runner->index(['help']);
        $result = CITestStreamFilter::$buffer;

        // make sure the result looks like basic help
        $this->assertStringContainsString('Displays basic usage information.', $result);
        $this->assertStringContainsString('help command_name', $result);
    }

    public function testHelpCommandDetails()
    {
        self::$runner->index(['help', 'session:migration']);
        $result = CITestStreamFilter::$buffer;

        // make sure the result looks like more detailed help
        $this->assertStringContainsString('Description:', $result);
        $this->assertStringContainsString('Usage:', $result);
        $this->assertStringContainsString('Options:', $result);
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
        $this->assertStringContainsString('Lists the available commands.', CITestStreamFilter::$buffer);
    }

    public function testBadCommand()
    {
        self::$runner->index(['bogus']);

        // make sure the result looks like a command list
        $this->assertStringContainsString('Command "bogus" not found', CITestStreamFilter::$buffer);
    }

    public function testRemapEmptyFirstParams()
    {
        self::$runner->_remap('anyvalue', null, 'list');
        $result = CITestStreamFilter::$buffer;

        // make sure the result looks like a command list
        $this->assertStringContainsString('Lists the available commands.', $result);
    }
}
