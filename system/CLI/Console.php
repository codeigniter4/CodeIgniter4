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

/**
 * @see \CodeIgniter\CLI\ConsoleTest
 */
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
        if ($tokens === []) {
            $tokens = service('superglobals')->server('argv', []);
        }

        $parser = new CommandLineParser($tokens);

        $arguments     = $parser->getArguments();
        $this->options = $parser->getOptions();

        $this->showHeader($this->hasParameterOption(['no-header']));

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

        return $commands->runCommand($this->command, $arguments, $this->options);
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
            && ! CLI::getInputOutput() instanceof NullInputOutput
            && defined('STDIN')
            && CLI::streamSupports('stream_isatty', STDIN);
    }
}
