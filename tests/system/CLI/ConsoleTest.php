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

use CodeIgniter\CodeIgniter;
use CodeIgniter\Config\DotEnv;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCLIConfig;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use CodeIgniter\Test\StreamFilterTrait;

/**
 * @internal
 *
 * @group Others
 */
final class ConsoleTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private DotEnv $env;

    protected function setUp(): void
    {
        parent::setUp();

        $this->env = new DotEnv(ROOTPATH);
        $this->env->load();

        // Set environment values that would otherwise stop the framework from functioning during tests.
        if (! isset($_SERVER['app.baseURL'])) {
            $_SERVER['app.baseURL'] = 'http://example.com/';
        }

        $this->app = new MockCodeIgniter(new MockCLIConfig());
        $this->app->initialize();
    }

    public function testHeader(): void
    {
        $console = new Console();
        $console->showHeader();
        $this->assertGreaterThan(
            0,
            strpos(
                $this->getStreamFilterBuffer(),
                sprintf('CodeIgniter v%s Command Line Tool', CodeIgniter::CI_VERSION)
            )
        );
    }

    public function testNoHeader(): void
    {
        $console = new Console();
        $console->showHeader(true);
        $this->assertSame('', $this->getStreamFilterBuffer());
    }

    public function testRun(): void
    {
        $this->initCLI();

        $console = new Console();
        $console->run();

        // make sure the result looks like a command list
        $this->assertStringContainsString('Lists the available commands.', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Displays basic usage information.', $this->getStreamFilterBuffer());
    }

    public function testBadCommand(): void
    {
        $this->initCLI('bogus');

        $console = new Console();
        $console->run();

        // make sure the result looks like a command list
        $this->assertStringContainsString('Command "bogus" not found', $this->getStreamFilterBuffer());
    }

    public function testHelpCommandDetails(): void
    {
        $this->initCLI('help', 'session:migration');

        $console = new Console();
        $console->run();

        // make sure the result looks like more detailed help
        $this->assertStringContainsString('Description:', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Usage:', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Options:', $this->getStreamFilterBuffer());
    }

    public function testHelpCommandUsingHelpOption(): void
    {
        $this->initCLI('env', '--help');

        (new Console())->run();

        $this->assertStringContainsString('env [<environment>]', $this->getStreamFilterBuffer());
        $this->assertStringContainsString(
            'Retrieves the current environment, or set a new one.',
            $this->getStreamFilterBuffer()
        );
    }

    public function testHelpOptionIsOnlyPassed(): void
    {
        $this->initCLI('--help');

        (new Console())->run();

        // Since calling `php spark` is the same as calling `php spark list`,
        // `php spark --help` should be the same as `php spark list --help`
        $this->assertStringContainsString('Lists the available commands.', $this->getStreamFilterBuffer());
    }

    public function testHelpArgumentAndHelpOptionCombined(): void
    {
        $this->initCLI('help', '--help');

        (new Console())->run();

        // Same as calling `php spark help` only
        $this->assertStringContainsString('Displays basic usage information.', $this->getStreamFilterBuffer());
    }

    /**
     * @param array $command
     */
    protected function initCLI(...$command): void
    {
        $_SERVER['argv'] = ['spark', ...$command];
        $_SERVER['argc'] = count($_SERVER['argv']);

        CLI::init();
    }
}
