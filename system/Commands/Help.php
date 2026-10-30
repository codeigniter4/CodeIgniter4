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
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\CLI\Input\Option;
use CodeIgniter\CLI\Output;

/**
 * Displays the basic usage information for a given command.
 */
#[Command(name: 'help', description: 'Displays basic usage information.', group: 'CodeIgniter')]
class Help extends AbstractCommand
{
    protected function configure(): void
    {
        $this->addArgument(new Argument(
            name: 'command_name',
            description: 'The command name.',
            default: $this->getName(),
        ));
    }

    protected function execute(array $arguments, array $options): int
    {
        $command = $arguments['command_name'];
        assert(is_string($command));

        $commands = $this->getCommandRunner();

        if (array_key_exists($command, $commands->getCommands())) {
            $commands->getCommand($command, legacy: true)->showHelp();

            return EXIT_SUCCESS;
        }

        if (! $commands->verifyCommand($command, legacy: false)) {
            return EXIT_ERROR;
        }

        $this->describeHelp($commands->getCommand($command, legacy: false), $this->getOutput());

        return EXIT_SUCCESS;
    }

    private function describeHelp(AbstractCommand $command, Output $output): void
    {
        $output->write(lang('CLI.helpUsage'), 'yellow');

        foreach ($command->getUsages() as $usage) {
            $output->write($this->addPadding($usage));
        }

        if ($command->getDescription() !== '') {
            $output->newLine();
            $output->write(lang('CLI.helpDescription'), 'yellow');
            $output->write($this->addPadding($command->getDescription()));
        }

        if ($command->getAliases() !== []) {
            $output->newLine();
            $output->write(lang('CLI.helpAliases'), 'yellow');

            foreach ($command->getAliases() as $alias) {
                $output->write($this->addPadding($alias));
            }
        }

        $maxPadding = $this->getMaxPadding($command);

        if ($command->getArgumentsDefinition() !== []) {
            $output->newLine();
            $output->write(lang('CLI.helpArguments'), 'yellow');

            foreach ($command->getArgumentsDefinition() as $argument => $definition) {
                $default = ! $definition->required && $this->shouldShowDefault($definition->default)
                    ? sprintf(' [default: %s]', $this->formatDefaultValue($definition->default))
                    : '';

                $output->write(sprintf(
                    '%s%s%s',
                    $output->color($this->addPadding($argument, 2, $maxPadding), 'green'),
                    $definition->description,
                    $output->color($default, 'yellow'),
                ));
            }
        }

        if ($command->getOptionsDefinition() !== []) {
            $output->newLine();
            $output->write(lang('CLI.helpOptions'), 'yellow');

            $hasShortcuts = $command->getShortcuts() !== [];

            foreach ($command->getOptionsDefinition() as $option => $definition) {
                $value = '';

                if ($definition->acceptsValue) {
                    $value = sprintf('=%s', strtoupper($definition->valueLabel ?? ''));

                    if (! $definition->requiresValue) {
                        $value = sprintf('[%s]', $value);
                    }
                }

                $optionString = sprintf(
                    '%s--%s%s%s',
                    $definition->shortcut !== null
                        ? sprintf('-%s, ', $definition->shortcut)
                        : ($hasShortcuts ? '    ' : ''),
                    $option,
                    $value,
                    $definition->negation !== null ? sprintf('|--%s', $definition->negation) : '',
                );

                $default = $this->shouldShowOptionDefault($definition)
                    ? sprintf(' [default: %s]', $this->formatDefaultValue($definition->default))
                    : '';

                $output->write(sprintf(
                    '%s%s%s%s',
                    $output->color($this->addPadding($optionString, 2, $maxPadding), 'green'),
                    $definition->description,
                    $output->color($default, 'yellow'),
                    $definition->isArray ? $output->color(' (multiple values allowed)', 'yellow') : '',
                ));
            }
        }
    }

    private function addPadding(string $item, int $before = 2, ?int $max = null): string
    {
        return str_pad(str_repeat(' ', $before) . $item, $max ?? (strlen($item) + $before));
    }

    private function getMaxPadding(AbstractCommand $command): int
    {
        $max = 0;

        foreach (array_keys($command->getArgumentsDefinition()) as $argument) {
            $max = max($max, strlen($argument));
        }

        $hasShortcuts = $command->getShortcuts() !== [];

        foreach ($command->getOptionsDefinition() as $option => $definition) {
            $optionLength = strlen($option) + 2 // Account for the "--" prefix on options.
                + ($definition->acceptsValue ? strlen($definition->valueLabel ?? '') + ($definition->requiresValue ? 1 : 3) : 0) // Account for the "=%s" value notation if the option accepts a value.
                + ($hasShortcuts ? 4 : 0) // Account for the "-%s, " shortcut notation if shortcuts are present.
                + ($definition->negation !== null ? 3 + strlen($definition->negation) : 0); // Account for the "|--no-%s" negation notation if a negation exists for this option.

            $max = max($max, $optionLength);
        }

        return $max + 4; // Account for the extra padding around the option/argument.
    }

    /**
     * Decides whether an option's default value is worth displaying.
     */
    private function shouldShowOptionDefault(Option $definition): bool
    {
        // Only value-accepting options carry a meaningful default. Plain flags
        // and negatable toggles are boolean by nature.
        if (! $definition->acceptsValue) {
            return false;
        }

        return $this->shouldShowDefault($definition->default);
    }

    /**
     * Whether a default value is concrete enough to display. `null` and the
     * empty string are treated as "no default".
     *
     * @param list<string>|string|null $value
     */
    private function shouldShowDefault(array|string|null $value): bool
    {
        return $value !== null && $value !== '';
    }

    /**
     * @param list<string>|string $value
     */
    private function formatDefaultValue(array|string $value): string
    {
        if (is_array($value)) {
            return sprintf('[%s]', implode(', ', array_map($this->formatDefaultValue(...), $value)));
        }

        return sprintf('"%s"', $value);
    }
}
