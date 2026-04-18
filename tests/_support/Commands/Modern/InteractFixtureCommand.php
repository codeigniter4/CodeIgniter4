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

namespace Tests\Support\Commands\Modern;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\CLI\Input\Option;

#[Command(name: 'test:interact', description: 'Fixture that mutates arguments and options in interact().', group: 'Fixtures')]
final class InteractFixtureCommand extends AbstractCommand
{
    /**
     * @var array<string, list<string>|string>
     */
    public array $executedArguments = [];

    /**
     * @var array<string, bool|list<string>|string|null>
     */
    public array $executedOptions = [];

    protected function configure(): void
    {
        $this
            ->addArgument(new Argument(name: 'name', default: 'anonymous'))
            ->addOption(new Option(name: 'force'));
    }

    protected function interact(array &$arguments, array &$options): void
    {
        // Supply a positional argument the caller omitted.
        if ($arguments === []) {
            $arguments[] = 'from-interact';
        }

        // Simulate the `--force` flag being passed so execute() sees it bound to `true`.
        $options['force'] = null;
    }

    protected function execute(array $arguments, array $options): int
    {
        $this->executedArguments = $arguments;
        $this->executedOptions   = $options;

        return EXIT_SUCCESS;
    }
}
