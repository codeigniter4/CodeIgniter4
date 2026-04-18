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
}
