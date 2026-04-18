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

namespace CodeIgniter\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Option;

/**
 * Lists the available commands.
 */
#[Command(name: 'list', description: 'Lists the available commands.', group: 'CodeIgniter')]
class ListCommands extends AbstractCommand
{
    protected function configure(): void
    {
        $this->addOption(new Option(
            name: 'simple',
            description: 'Prints a list of commands with no other information.',
        ));
    }

    protected function execute(array $arguments, array $options): int
    {
        if ($options['simple'] === true) {
            return $this->describeCommandsSimple();
        }

        return $this->describeCommandsDetailed();
    }

    private function describeCommandsSimple(): int
    {
        // Legacy takes precedence on key collision so the listing reflects the
        // command that would actually be invoked.
        $commands = array_keys(
            $this->getCommandRunner()->getCommands() + $this->getCommandRunner()->getModernCommands(),
        );
        sort($commands);

        foreach ($commands as $command) {
            CLI::write($command);
        }

        return EXIT_SUCCESS;
    }

    private function describeCommandsDetailed(): int
    {
        CLI::write(lang('CLI.helpUsage'), 'yellow');
        CLI::write($this->addPadding('command [options] [--] [arguments]'));

        $entries = [];
        $maxPad  = 0;

        // Legacy takes precedence on key collision so the listing reflects the
        // command that would actually be invoked.
        $all = $this->getCommandRunner()->getCommands() + $this->getCommandRunner()->getModernCommands();

        foreach ($all as $command => $details) {
            $maxPad = max($maxPad, strlen($command) + 4);

            $entries[] = [$details['group'], $command, $details['description']];
        }

        usort($entries, static function (array $a, array $b): int {
            $cmp = strcmp($a[0], $b[0]);

            if ($cmp !== 0) {
                return $cmp;
            }

            return strcmp($a[1], $b[1]);
        });

        $groups = [];

        foreach ($entries as [$group, $command, $description]) {
            $groups[$group][] = [$command, $description];
        }

        CLI::newLine();
        CLI::write(lang('CLI.helpAvailableCommands'), 'yellow');

        $firstGroup = array_key_first($groups);

        foreach ($groups as $group => $commands) {
            if ($group !== $firstGroup) {
                CLI::newLine();
            }

            CLI::write($group, 'yellow');

            foreach ($commands as $command) {
                CLI::write(sprintf(
                    '%s%s',
                    CLI::color($this->addPadding($command[0], 2, $maxPad), 'green'),
                    CLI::wrap($command[1], 0, $maxPad),
                ));
            }
        }

        return EXIT_SUCCESS;
    }

    private function addPadding(string $item, int $before = 2, ?int $max = null): string
    {
        return str_pad(str_repeat(' ', $before) . $item, $max ?? (strlen($item) + $before));
    }
}
