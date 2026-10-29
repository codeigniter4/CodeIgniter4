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

use CodeIgniter\CLI\Exceptions\CommandNotAvailableException;
use CodeIgniter\CLI\Exceptions\OptionValueMismatchException;
use CodeIgniter\CodeIgniter;
use CodeIgniter\Config\DotEnv;
use CodeIgniter\Config\Services;
use CodeIgniter\Events\Events;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCLIConfig;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use CodeIgniter\Test\Mock\MockInputOutput;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Commands\Modern\UnavailableFixtureCommand;
use Tests\Support\Commands\Modern\VerbosityProbeCommand;

/**
 * @internal
 */
#[CoversClass(Console::class)]
#[Group('Others')]
final class ConsoleTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        parent::setUp();

        Services::injectMock('superglobals', new Superglobals());
        CLI::reset();
        CLI::init();

        (new DotEnv(ROOTPATH))->load();
        $this->clearShellVerbosity();

        $this->app = new MockCodeIgniter(new MockCLIConfig());
        $this->app->initialize();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        service('superglobals')->setServer('CODEIGNITER_SCREAM_DEPRECATIONS', '1');
        $this->clearShellVerbosity();
        Services::resetSingle('commands');

        CLI::reset();
    }

    private function getUndecoratedBuffer(): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()) ?? '';
    }

    private function getUndecoratedIoOutput(MockInputOutput $io): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $io->getOutput()) ?? '';
    }

    private function useInputs(string ...$inputs): MockInputOutput
    {
        $io = new MockInputOutput();
        $io->setInputs($inputs);
        CLI::setInputOutput($io);

        return $io;
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

    #[DataProvider('provideHeaderlessCommandOmitsHeader')]
    public function testHeaderlessCommandOmitsHeader(string $command): void
    {
        $this->initializeConsole($command);
        (new Console())->run();

        $this->assertSame(
            <<<'EOT'

                Ran test:headerless.

                EOT,
            $this->getStreamFilterBuffer(),
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideHeaderlessCommandOmitsHeader(): iterable
    {
        yield 'name' => ['test:headerless'];

        yield 'alias' => ['test:quiet'];
    }

    /**
     * @param list<string> $tokens
     */
    #[DataProvider('provideHelpForHeaderlessCommandShowsHeader')]
    public function testHelpForHeaderlessCommandShowsHeader(array $tokens): void
    {
        $this->initializeConsole(...$tokens);
        (new Console())->run();

        $this->assertStringContainsString(
            sprintf('CodeIgniter v%s Command Line Tool', CodeIgniter::CI_VERSION),
            $this->getStreamFilterBuffer(),
        );
    }

    /**
     * @return iterable<string, array{list<string>}>
     */
    public static function provideHelpForHeaderlessCommandShowsHeader(): iterable
    {
        yield 'help command' => [['help', 'test:headerless']];

        yield 'help option' => [['test:headerless', '--help']];

        yield 'help shortcut on alias' => [['test:quiet', '-h']];
    }

    public function testNoHeaderOptionAppliesAlongsideHelpOption(): void
    {
        $this->initializeConsole('env', '--help', '--no-header');
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

    public function testUnknownCommandRunsConfirmedSuggestion(): void
    {
        service('superglobals')->unsetServer('CODEIGNITER_SCREAM_DEPRECATIONS');

        $this->initializeConsole('app:inf', '--no-header');
        $io = $this->useInputs('y');

        $console  = new Console();
        $exitCode = $console->run();

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertSame('app:info', $console->getCommand());
        $this->assertSame(
            sprintf(
                <<<'EOT'

                    Command "app:inf" not found.

                    Run "app:info" instead? [y, n]: y

                    CodeIgniter Version: %s

                    EOT,
                CodeIgniter::CI_VERSION,
            ),
            $this->getUndecoratedIoOutput($io),
        );
    }

    public function testUnknownCommandDeclinedSuggestionExitsWithError(): void
    {
        $this->initializeConsole('app:inf', '--no-header');
        $io = $this->useInputs('n');

        $console  = new Console();
        $exitCode = $console->run();

        $this->assertSame(EXIT_ERROR, $exitCode);
        $this->assertSame('app:inf', $console->getCommand());
        $this->assertSame(
            <<<'EOT'

                Command "app:inf" not found.

                Run "app:info" instead? [y, n]: n

                EOT,
            $this->getUndecoratedIoOutput($io),
        );
    }

    public function testUnknownCommandRunsSelectedSuggestion(): void
    {
        $this->initializeConsole('clear', '--no-header');
        $io = $this->useInputs('1');

        $console  = new Console();
        $exitCode = $console->run();

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertSame('cache:clear', $console->getCommand());
        $this->assertSame(
            <<<'EOT'

                Command "clear" not found.

                Select a command to run instead:
                  [1]  cache:clear
                  [2]  debugbar:clear
                  [3]  logs:clear
                  [4]  none of these
                Number [1]: 1

                Cache cleared using the "file" driver.

                EOT,
            $this->getUndecoratedIoOutput($io),
        );
    }

    public function testUnknownCommandSelectingNoneExitsWithError(): void
    {
        $this->initializeConsole('clear', '--no-header');
        $io = $this->useInputs('4');

        $console  = new Console();
        $exitCode = $console->run();

        $this->assertSame(EXIT_ERROR, $exitCode);
        $this->assertSame('clear', $console->getCommand());
        $this->assertSame(
            <<<'EOT'

                Command "clear" not found.

                Select a command to run instead:
                  [1]  cache:clear
                  [2]  debugbar:clear
                  [3]  logs:clear
                  [4]  none of these
                Number [1]: 4

                EOT,
            $this->getUndecoratedIoOutput($io),
        );
    }

    public function testUnknownCommandDoesNotOfferHiddenCommand(): void
    {
        $this->initializeConsole('fixture:hiddenn', '--no-header');
        $io = $this->useInputs();

        $this->assertSame(EXIT_ERROR, (new Console())->run());
        $this->assertSame(
            <<<'EOT'

                Command "fixture:hiddenn" not found.

                EOT,
            $this->getUndecoratedIoOutput($io),
        );
    }

    public function testUnknownCommandDoesNotPromptWhenNotInteractive(): void
    {
        $this->initializeConsole('lst', '--no-header', '--no-interaction');
        $exitCode = (new Console())->run();

        $this->assertSame(EXIT_ERROR, $exitCode);
        $this->assertSame(
            <<<'EOT'

                Command "lst" not found.

                Did you mean this?
                    list

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testUnknownCommandDoesNotPromptWithNullInputOutput(): void
    {
        $this->initializeConsole('lst', '--no-header');
        CLI::setInputOutput(new NullInputOutput());

        $console  = new Console();
        $exitCode = $console->run();

        $this->assertSame(EXIT_ERROR, $exitCode);
        $this->assertSame('lst', $console->getCommand());
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

        $this->assertStringContainsString('env [options] [--] [<environment>]', $this->getStreamFilterBuffer());
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

    public function testHelpShortcutStripsOptionsMeantForTargetCommand(): void
    {
        $this->initializeConsole('serve', '--host=example.com', '--help');
        $exitCode = (new Console())->run();

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertStringContainsString('serve [options]', $this->getStreamFilterBuffer());
    }

    public function testHelpArgumentAndHelpOptionCombined(): void
    {
        $this->initializeConsole('help', '--help');
        (new Console())->run();

        // Same as calling `php spark help` only
        $this->assertStringContainsString('Displays basic usage information.', $this->getStreamFilterBuffer());
    }

    public function testRunRoutesDiscoveredLegacyCommandThroughRunLegacy(): void
    {
        service('superglobals')->unsetServer('CODEIGNITER_SCREAM_DEPRECATIONS');

        // `app:info` is a legacy BaseCommand fixture. Console must take the
        // legacy branch of run() and delegate to Commands::runLegacy().
        $this->initializeConsole('app:info');
        $exitCode = (new Console())->run();

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertStringContainsString(
            sprintf('CodeIgniter Version: %s', CodeIgniter::CI_VERSION),
            $this->getStreamFilterBuffer(),
        );
    }

    public function testConsoleReturnsTheLastExecutedCommand(): void
    {
        $console = new Console();
        $this->assertSame('', $console->getCommand());

        $this->initializeConsole();
        $console->run();
        $this->assertSame('list', $console->getCommand());

        $this->initializeConsole('help');
        $console->run();
        $this->assertSame('help', $console->getCommand());

        $this->initializeConsole('list');
        $console->run();
        $this->assertSame('list', $console->getCommand());
    }

    /**
     * @param list<string> $tokens
     */
    #[DataProvider('provideVerbosityFlagsSetTheLevel')]
    public function testVerbosityFlagsSetTheLevel(array $tokens, string $expected): void
    {
        $this->initializeConsole('test:verbosity', '--no-header', ...$tokens);
        $io = $this->useInputs();

        (new Console())->run();

        $this->assertSame($expected, $this->getUndecoratedIoOutput($io));
    }

    /**
     * @return iterable<string, array{list<string>, string}>
     */
    public static function provideVerbosityFlagsSetTheLevel(): iterable
    {
        yield 'none' => [[], "\nquiet\nnormal\nerror\n"];

        yield '--silent' => [['--silent'], ''];

        yield '--silent wins over -q and -v' => [['-q', '-v', '--silent'], ''];

        yield '-q' => [['-q'], "\nquiet\nerror\n"];

        yield '--quiet' => [['--quiet'], "\nquiet\nerror\n"];

        yield '-q wins over -v' => [['-q', '-v'], "\nquiet\nerror\n"];

        yield '-v' => [['-v'], "\nquiet\nnormal\nverbose\nerror\n"];

        yield '--verbose' => [['--verbose'], "\nquiet\nnormal\nverbose\nerror\n"];

        yield '-vv' => [['-vv'], "\nquiet\nnormal\nverbose\nvery verbose\nerror\n"];

        yield '-v -v' => [['-v', '-v'], "\nquiet\nnormal\nverbose\nvery verbose\nerror\n"];

        yield '-vvv' => [['-vvv'], "\nquiet\nnormal\nverbose\nvery verbose\ndebug\nerror\n"];

        yield '-vvvv' => [['-vvvv'], "\nquiet\nnormal\nverbose\nvery verbose\ndebug\nerror\n"];
    }

    public function testQuietDropsTheHeader(): void
    {
        $this->initializeConsole('test:verbosity', '-q');
        $io = $this->useInputs();

        (new Console())->run();

        $this->assertSame("\nquiet\nerror\n", $this->getUndecoratedIoOutput($io));
    }

    public function testModernCommandSeesTheCollapsedFlags(): void
    {
        VerbosityProbeCommand::reset();
        $this->initializeConsole('test:verbosity', '--no-header', '--silent', '--silent', '-q', '-q', '-v', '-vv');
        $this->useInputs();

        $exitCode = (new Console())->run();

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertSame(['silent' => true, 'quiet' => true, 'verbose' => true], VerbosityProbeCommand::$observedOptions);
    }

    public function testModernCommandAcceptsANumericShortcut(): void
    {
        VerbosityProbeCommand::reset();
        $this->initializeConsole('test:verbosity', '--no-header', '-1', '-v');
        $this->useInputs();

        $exitCode = (new Console())->run();

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertTrue(VerbosityProbeCommand::$observedOnce);
        $this->assertTrue(VerbosityProbeCommand::$observedOptions['verbose']);
    }

    public function testLegacyCommandReceivesTheRawTokens(): void
    {
        $commands = $this->getMockBuilder(Commands::class)->onlyMethods(['runLegacy'])->getMock();
        $commands->expects($this->once())->method('runLegacy')->with('app:info', ['vv' => null, 'no-interaction' => null])->willReturn(EXIT_SUCCESS);
        Services::injectMock('commands', $commands);

        $this->initializeConsole('app:info', '-vv', '--no-interaction');

        (new Console())->run();
    }

    public function testQuietAppliesToLegacyCommands(): void
    {
        service('superglobals')->unsetServer('CODEIGNITER_SCREAM_DEPRECATIONS');

        $this->initializeConsole('app:info', '-q');
        $io = $this->useInputs();

        $this->assertSame(EXIT_SUCCESS, (new Console())->run());
        $this->assertSame('', $io->getOutput());
    }

    public function testQuietDoesNotPromptForAnAlternative(): void
    {
        $this->initializeConsole('lst', '--no-header', '-q');
        $io = $this->useInputs('y');

        $this->assertSame(EXIT_ERROR, (new Console())->run());
        $this->assertSame(
            <<<'EOT'

                Command "lst" not found.

                Did you mean this?
                    list

                EOT,
            $this->getUndecoratedIoOutput($io),
        );
    }

    /**
     * @param list<string> $tokens
     */
    #[DataProvider('provideShellVerbosityIsTheFallback')]
    public function testShellVerbosityIsTheFallback(string $shellVerbosity, array $tokens, string $expected): void
    {
        putenv(sprintf('SHELL_VERBOSITY=%s', $shellVerbosity));
        $this->initializeConsole('test:verbosity', '--no-header', ...$tokens);
        $io = $this->useInputs();

        (new Console())->run();

        $this->assertSame($expected, $this->getUndecoratedIoOutput($io));
    }

    /**
     * @return iterable<string, array{string, list<string>, string}>
     */
    public static function provideShellVerbosityIsTheFallback(): iterable
    {
        yield 'quiet' => ['-1', [], "\nquiet\nerror\n"];

        yield 'silent' => ['-2', [], ''];

        yield 'verbose' => ['1', [], "\nquiet\nnormal\nverbose\nerror\n"];

        yield 'flag wins' => ['-1', ['-v'], "\nquiet\nnormal\nverbose\nerror\n"];

        yield 'invalid' => ['7', [], "\nquiet\nnormal\nerror\n"];
    }

    public function testShellVerbosityIsMirroredDuringTheRunAndRemovedAfter(): void
    {
        VerbosityProbeCommand::reset();
        $this->initializeConsole('test:verbosity', '--no-header', '-vv');
        $this->useInputs();

        (new Console())->run();

        $this->assertSame('2', VerbosityProbeCommand::$observedShellVerbosity);
        $this->assertFalse(getenv('SHELL_VERBOSITY'));
        $this->assertArrayNotHasKey('SHELL_VERBOSITY', $_ENV);
        $this->assertNull(service('superglobals')->server('SHELL_VERBOSITY'));
        $this->assertSame(Verbosity::Normal, CLI::getOutput()->getVerbosity());
    }

    public function testUncaughtThrowableKeepsTheLevelForTheExceptionHandler(): void
    {
        UnavailableFixtureCommand::$available = false;
        $this->initializeConsole('test:unavailable', '--no-header', '-v');
        $this->useInputs();

        try {
            (new Console())->run();
            $this->fail('The command should have thrown.');
        } catch (CommandNotAvailableException) {
            $this->assertSame(Verbosity::Verbose, CLI::getOutput()->getVerbosity());
            $this->assertFalse(getenv('SHELL_VERBOSITY'));
        } finally {
            UnavailableFixtureCommand::reset();
        }
    }

    public function testInitializeReturnsTheConsole(): void
    {
        $console = new Console();

        $this->assertSame($console, $console->initialize());
    }

    public function testUncaughtThrowableRestoresTheLevelForExplicitTokens(): void
    {
        UnavailableFixtureCommand::$available = false;
        $this->useInputs();

        try {
            (new Console())->run(['spark', 'test:unavailable', '--no-header', '-v']);
            $this->fail('The command should have thrown.');
        } catch (CommandNotAvailableException) {
            $this->assertSame(Verbosity::Normal, CLI::getOutput()->getVerbosity());
        } finally {
            UnavailableFixtureCommand::reset();
        }
    }

    /**
     * @param list<string> $tokens
     */
    #[DataProvider('provideVerbosityFlagWithValueIsRejected')]
    public function testVerbosityFlagWithValueIsRejected(array $tokens): void
    {
        $this->initializeConsole('test:verbosity', '--no-header', ...$tokens);
        $this->useInputs();

        $this->expectException(OptionValueMismatchException::class);

        (new Console())->run();
    }

    /**
     * @return iterable<string, array{list<string>}>
     */
    public static function provideVerbosityFlagWithValueIsRejected(): iterable
    {
        yield '-q value' => [['-q', 'extra']];

        yield '-vv value' => [['-vv', 'extra']];

        yield '-v -v value' => [['-v', '-v', 'extra']];

        yield '--silent=value' => [['--silent=extra']];
    }

    public function testShellVerbosityIsRestoredAfterTheRun(): void
    {
        putenv('SHELL_VERBOSITY=1');
        VerbosityProbeCommand::reset();
        $this->initializeConsole('test:verbosity', '--no-header', '-q');
        $this->useInputs();

        (new Console())->run();

        $this->assertSame('-1', VerbosityProbeCommand::$observedShellVerbosity);
        $this->assertSame('1', getenv('SHELL_VERBOSITY'));
        $this->assertArrayNotHasKey('SHELL_VERBOSITY', $_ENV);
        $this->assertNull(service('superglobals')->server('SHELL_VERBOSITY'));
    }

    public function testShellVerbosityRestoresValuesPresentOnlyInSuperglobals(): void
    {
        $_ENV['SHELL_VERBOSITY'] = '2';
        service('superglobals')->setServer('SHELL_VERBOSITY', '1');
        VerbosityProbeCommand::reset();
        $this->initializeConsole('test:verbosity', '--no-header');
        $io = $this->useInputs();

        (new Console())->run();

        $this->assertStringContainsString('very verbose', $this->getUndecoratedIoOutput($io));
        $this->assertSame('2', VerbosityProbeCommand::$observedShellVerbosity);
        $this->assertFalse(getenv('SHELL_VERBOSITY'));
        $this->assertSame('2', $_ENV['SHELL_VERBOSITY']);
        $this->assertSame('1', service('superglobals')->server('SHELL_VERBOSITY'));
    }

    private function clearShellVerbosity(): void
    {
        putenv('SHELL_VERBOSITY');
        unset($_ENV['SHELL_VERBOSITY']);
        service('superglobals')->unsetServer('SHELL_VERBOSITY');
    }

    private function initializeConsole(string ...$tokens): void
    {
        service('superglobals')
            ->setServer('argv', ['spark', ...$tokens])
            ->setServer('argc', count($tokens) + 1);

        CLI::init();
    }
}
