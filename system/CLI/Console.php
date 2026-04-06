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
    /**
     * @internal
     */
    public const DEFAULT_COMMAND = 'list';

    private string $command = '';

    /**
     * @var array<string, string|null>
     */
    private array $options = [];

    /**
     * Runs the current command discovered on the CLI.
     *
     * @param list<string> $tokens
     *
     * @return int|null Exit code or null for legacy commands that don't return an exit code.
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
        unset($this->options['no-header']);

        if ($this->hasParameterOption(['help'])) {
            unset($this->options['help']);

            if ($arguments === []) {
                $arguments = ['help', self::DEFAULT_COMMAND];
            } elseif ($arguments[0] !== 'help') {
                array_unshift($arguments, 'help');
            }
        }

        $this->command = array_shift($arguments) ?? self::DEFAULT_COMMAND;

        return service('commands')->run($this->command, array_merge($arguments, $this->options));
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
