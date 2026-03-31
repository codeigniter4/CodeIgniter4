<?php

declare(strict_types=1);

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
use CodeIgniter\Config\Services;
use CodeIgniter\Events\Events;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCLIConfig;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ConsoleTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        parent::setUp();

        Services::injectMock('superglobals', new Superglobals());
        CLI::init();

        (new DotEnv(ROOTPATH))->load();

        $this->app = new MockCodeIgniter(new MockCLIConfig());
        $this->app->initialize();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        CLI::reset();
    }

    public function testHeaderShowsNormally(): void
    {
        $this->initializeConsole();
        (new Console())->run();

        $this->assertStringContainsString(
            sprintf('CodeIgniter v%s Command Line Tool', CodeIgniter::CI_VERSION),
            $this->getStreamFilterBuffer(),
        );
    }

    public function testHeaderDoesNotShowOnNoHeader(): void
    {
        $this->initializeConsole('--no-header');
        (new Console())->run();

        $this->assertStringNotContainsString(
            sprintf('CodeIgniter v%s Command Line Tool', CodeIgniter::CI_VERSION),
            $this->getStreamFilterBuffer(),
        );
    }

    public function testRun(): void
    {
        $this->initializeConsole();
        (new Console())->run();

        $this->assertStringContainsString('Lists the available commands.', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Displays basic usage information.', $this->getStreamFilterBuffer());
    }

    public function testRunEventsPreCommand(): void
    {
        $result = '';
        Events::on('pre_command', static function () use (&$result): void {
            $result = 'fired';
        });

        $this->initializeConsole();
        (new Console())->run();

        $this->assertEventTriggered('pre_command');
        $this->assertSame('fired', $result);
    }

    public function testRunEventsPostCommand(): void
    {
        $result = '';
        Events::on('post_command', static function () use (&$result): void {
            $result = 'fired';
        });

        $this->initializeConsole();
        (new Console())->run();

        $this->assertEventTriggered('post_command');
        $this->assertSame('fired', $result);
    }

    public function testBadCommand(): void
    {
        $this->initializeConsole('bogus');
        (new Console())->run();

        $this->assertStringContainsString('Command "bogus" not found', $this->getStreamFilterBuffer());
    }

    public function testHelpCommandDetails(): void
    {
        $this->initializeConsole('help', 'make:migration');
        (new Console())->run();

        $this->assertStringContainsString('Description:', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Usage:', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Options:', $this->getStreamFilterBuffer());
    }

    public function testHelpCommandUsingHelpOption(): void
    {
        $this->initializeConsole('env', '--help');
        (new Console())->run();

        $this->assertStringContainsString('env [<environment>]', $this->getStreamFilterBuffer());
        $this->assertStringContainsString(
            'Retrieves the current environment, or set a new one.',
            $this->getStreamFilterBuffer(),
        );
    }

    public function testHelpOptionIsOnlyPassed(): void
    {
        $this->initializeConsole('--help');
        (new Console())->run();

        // Since calling `php spark` is the same as calling `php spark list`,
        // `php spark --help` should be the same as `php spark list --help`
        $this->assertStringContainsString('Lists the available commands.', $this->getStreamFilterBuffer());
    }

    public function testHelpArgumentAndHelpOptionCombined(): void
    {
        $this->initializeConsole('help', '--help');
        (new Console())->run();

        // Same as calling `php spark help` only
        $this->assertStringContainsString('Displays basic usage information.', $this->getStreamFilterBuffer());
    }

    private function initializeConsole(string ...$tokens): void
    {
        service('superglobals')
            ->setServer('argv', ['spark', ...$tokens])
            ->setServer('argc', count($tokens) + 1);

        CLI::init();
    }
}
