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
use CodeIgniter\CLI\Input\Option;
use CodeIgniter\CLI\Output;

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
        $output = $this->getOutput();

        if ($options['simple'] === true) {
            return $this->describeCommandsSimple($output);
        }

        return $this->describeCommandsDetailed($output);
    }

    private function describeCommandsSimple(Output $output): int
    {
        // Legacy takes precedence on key collision so the listing reflects the
        // command that would actually be invoked.
        $runner   = $this->getCommandRunner();
        $commands = array_filter(
            array_keys($runner->getCommands() + $runner->getModernCommands() + $runner->getCommandAliases()),
            static fn (string $command): bool => ! $runner->isHiddenCommand($command),
        );
        sort($commands);

        foreach ($commands as $command) {
            $output->write($command);
        }

        return EXIT_SUCCESS;
    }

    private function describeCommandsDetailed(Output $output): int
    {
        $output->write(lang('CLI.helpUsage'), 'yellow');
        $output->write($this->addPadding('command [options] [--] [arguments]'));

        $entries = [];
        $maxPad  = 0;

        // Legacy takes precedence on key collision so the listing reflects the
        // command that would actually be invoked.
        $runner = $this->getCommandRunner();
        $modern = $runner->getModernCommands();
        $all    = $runner->getCommands() + $modern;

        foreach ($all as $command => $details) {
            if ($runner->isHiddenCommand($command)) {
                continue;
            }

            $maxPad = max($maxPad, strlen($command) + 4);

            $entries[] = [$details['group'], $command, $details['description']];
        }

        // Aliases are listed as their own rows under the group of the command they resolve to.
        foreach ($runner->getCommandAliases() as $alias => $canonical) {
            if ($runner->isHiddenCommand($alias)) {
                continue;
            }

            $maxPad = max($maxPad, strlen($alias) + 4);

            $entries[] = [$modern[$canonical]['group'], $alias, lang('CLI.commandAlias', [$canonical])];
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

        $output->newLine();
        $output->write(lang('CLI.helpAvailableCommands'), 'yellow');

        $firstGroup = array_key_first($groups);

        foreach ($groups as $group => $commands) {
            if ($group !== $firstGroup) {
                $output->newLine();
            }

            $output->write($group, 'yellow');

            foreach ($commands as $command) {
                $output->write(sprintf(
                    '%s%s',
                    $output->color($this->addPadding($command[0], 2, $maxPad), 'green'),
                    $output->wrap($command[1], 0, $maxPad),
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
