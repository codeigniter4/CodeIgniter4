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
use Config\App;
use Config\Services;
use Throwable;

class Console
{
    private const DEFAULT_COMMAND = 'list';

    private string $command = '';

    /**
     * @var array<string, list<string|null>|string|null>
     */
    private array $options = [];

    /**
     * Runs the current command discovered on the CLI.
     *
     * @param list<string> $tokens
     *
     * @return int Exit code
     */
    public function run(array $tokens = [])
    {
        $fromArgv = $tokens === [];

        if ($fromArgv) {
            $tokens = service('superglobals')->server('argv', []);
        }

        $parser = new CommandLineParser($tokens);

        $arguments     = $parser->getArguments();
        $this->options = $parser->getOptions();

        $output                       = CLI::getOutput();
        $previousVerbosity            = $output->getVerbosity();
        $previousShellVerbosity       = getenv('SHELL_VERBOSITY');
        $previousEnvShellVerbosity    = $_ENV['SHELL_VERBOSITY'] ?? null;
        $previousServerShellVerbosity = service('superglobals')->server('SHELL_VERBOSITY');

        $output->setVerbosity($this->resolveVerbosity());
        $this->setShellVerbosity($output->getVerbosity()->value);

        try {
            $exitCode = $this->dispatch($arguments);
        } catch (Throwable $e) {
            // The spark run keeps the level, so the exception handler renders the uncaught throwable at it.
            if (! $fromArgv) {
                $output->setVerbosity($previousVerbosity);
            }

            throw $e;
        } finally {
            $this->restoreShellVerbosity($previousShellVerbosity, $previousEnvShellVerbosity, $previousServerShellVerbosity);
        }

        $output->setVerbosity($previousVerbosity);

        return $exitCode;
    }

    public function initialize(): static
    {
        Services::createRequest(config(App::class), true);
        service('routes')->loadRoutes();

        return $this;
    }

    /**
     * Returns the command that is being executed.
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * Displays basic information about the Console.
     *
     * @return void
     */
    public function showHeader(bool $suppress = false)
    {
        if ($suppress) {
            return;
        }

        CLI::write(sprintf(
            'CodeIgniter v%s Command Line Tool - Server Time: %s',
            CodeIgniter::CI_VERSION,
            date('Y-m-d H:i:s \\U\\T\\CP'),
        ), 'green');
        CLI::newLine();
    }

    /**
     * @param list<string> $arguments
     */
    private function dispatch(array $arguments): int
    {
        $noHeader = $this->hasParameterOption(['no-header']);

        if ($this->hasParameterOption(['help', 'h'])) {
            if ($arguments === []) {
                $arguments = ['help', self::DEFAULT_COMMAND];
            } elseif ($arguments[0] !== 'help') {
                array_unshift($arguments, 'help');
            }

            // Options supplied alongside --help were meant for the target command,
            // not for `help` itself. Dropping them avoids feeding unknown options
            // into the modern command pipeline's validator.
            $this->options = [];
        }

        /** @var Commands $commands */
        $commands = service('commands');

        $this->command = array_shift($arguments) ?? self::DEFAULT_COMMAND;

        $this->showHeader($noHeader || $commands->isHeaderlessCommand($this->command));

        if (
            $this->isInteractive()
            && ! $commands->hasLegacyCommand($this->command)
            && ! $commands->hasModernCommand($this->command)
        ) {
            $alternatives = $commands->getCommandAlternatives($this->command);

            if ($alternatives !== []) {
                $alternative = $this->chooseAlternative($alternatives);

                if ($alternative === null) {
                    return EXIT_ERROR;
                }

                $this->command = $alternative;
            }
        }

        if ($commands->hasLegacyCommand($this->command)) {
            $legacyOptions = $this->options;
            unset($legacyOptions['no-header']);

            return $commands->runLegacy($this->command, array_merge($arguments, $legacyOptions));
        }

        return $commands->runCommand($this->command, $arguments, $this->normalizeVerbosityOptions(), CLI::getOutput());
    }

    /**
     * Resolves the level from `--silent`, `--quiet`, the count of `-v`, or else `SHELL_VERBOSITY`.
     */
    private function resolveVerbosity(): Verbosity
    {
        $quiet = false;
        $count = 0;

        foreach ($this->options as $name => $value) {
            $option = $this->resolveVerbosityOption($name);

            if ($option === 'silent') {
                return Verbosity::Silent;
            }

            if ($option === 'quiet') {
                $quiet = true;
            } elseif ($option === 'verbose') {
                $count += ($name === 'verbose' ? 1 : strlen($name)) * (is_array($value) ? count($value) : 1);
            }
        }

        if ($quiet) {
            return Verbosity::Quiet;
        }

        if ($count > 0) {
            return Verbosity::from(min($count, Verbosity::Debug->value));
        }

        return Verbosity::tryFrom((int) env('SHELL_VERBOSITY', 0)) ?? Verbosity::Normal;
    }

    /**
     * Collapses repeated `--silent`, `-q` and `-v` tokens into the single `silent`, `quiet` and `verbose` flags of a modern command, keeping the first value so that validation rejects it.
     *
     * @return array<string, list<string|null>|string|null>
     */
    private function normalizeVerbosityOptions(): array
    {
        $options = [];
        $flags   = [];

        foreach ($this->options as $name => $value) {
            $option = $this->resolveVerbosityOption($name);

            if ($option === null) {
                $options[$name] = $value;

                continue;
            }

            $flags[$option] ??= null;

            foreach (is_array($value) ? $value : [$value] as $occurrence) {
                $flags[$option] ??= $occurrence;
            }
        }

        return $options + $flags;
    }

    /**
     * Returns `null` for an option unrelated to verbosity.
     *
     * @return 'quiet'|'silent'|'verbose'|null
     */
    private function resolveVerbosityOption(int|string $name): ?string
    {
        return match (true) {
            $name === 'silent'                                              => 'silent',
            $name === 'quiet', $name === 'q'                                => 'quiet',
            $name === 'verbose', preg_match('/^v+$/', (string) $name) === 1 => 'verbose',
            default                                                         => null,
        };
    }

    /**
     * Mirrors the level to `SHELL_VERBOSITY` for child processes.
     */
    private function setShellVerbosity(int $value): void
    {
        putenv(sprintf('SHELL_VERBOSITY=%d', $value));
        $_ENV['SHELL_VERBOSITY'] = $value;
        service('superglobals')->setServer('SHELL_VERBOSITY', $value);
    }

    /**
     * Puts back each store of `SHELL_VERBOSITY`, removing the variable where it was absent (`false` or `null`).
     *
     * @param array<array-key, mixed>|float|int|string|null $server
     */
    private function restoreShellVerbosity(false|string $process, mixed $env, array|float|int|string|null $server): void
    {
        putenv($process === false ? 'SHELL_VERBOSITY' : sprintf('SHELL_VERBOSITY=%s', $process));

        if ($env === null) {
            unset($_ENV['SHELL_VERBOSITY']);
        } else {
            $_ENV['SHELL_VERBOSITY'] = $env;
        }

        if ($server === null) {
            service('superglobals')->unsetServer('SHELL_VERBOSITY');
        } else {
            service('superglobals')->setServer('SHELL_VERBOSITY', $server);
        }
    }

    /**
     * Asks which suggested command to run instead, returning `null` when the user declines.
     *
     * @param list<string> $alternatives
     */
    private function chooseAlternative(array $alternatives): ?string
    {
        CLI::error(lang('CLI.commandNotFound', [$this->command]));
        CLI::newLine();

        if (count($alternatives) === 1) {
            return CLI::prompt(lang('CLI.altCommandRun', [$alternatives[0]]), ['y', 'n']) === 'y' ? $alternatives[0] : null;
        }

        $chosen = (int) CLI::promptByKey(lang('CLI.altCommandSelect'), [...$alternatives, lang('CLI.altCommandNone')]);

        return $alternatives[$chosen] ?? null;
    }

    /**
     * Checks whether any of the options are present in the command line.
     *
     * @param list<string> $options
     */
    private function hasParameterOption(array $options): bool
    {
        foreach ($options as $option) {
            if (array_key_exists($option, $this->options)) {
                return true;
            }
        }

        return false;
    }

    private function isInteractive(): bool
    {
        return ! $this->hasParameterOption(['no-interaction', 'N'])
            && ! CLI::getOutput()->isQuiet()
            && ! CLI::getInputOutput() instanceof NullInputOutput
            && defined('STDIN')
            && CLI::streamSupports('stream_isatty', STDIN);
    }
}
