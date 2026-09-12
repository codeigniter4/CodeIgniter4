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

namespace CodeIgniter\Commands\Generators;

use CodeIgniter\CLI\AbstractGeneratorCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Attributes\GeneratorCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Option;

#[Command(name: 'make:command', description: 'Generates a new spark command.', group: 'Generators')]
#[GeneratorCommand(
    component: 'Command',
    template: 'command.tpl.php',
    directory: 'Commands',
    classNameLang: 'CLI.generator.className.command',
)]
class CommandGenerator extends AbstractGeneratorCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption(new Option(
                name: 'command',
                shortcut: 'c',
                description: 'The command name.',
                requiresValue: true,
                valueLabel: 'name',
                default: 'command:name',
            ))
            ->addOption(new Option(
                name: 'type',
                shortcut: 't',
                description: 'The command type: "basic" or "generator".',
                requiresValue: true,
                default: 'basic',
            ))
            ->addOption(new Option(
                name: 'group',
                shortcut: 'g',
                description: 'The command group. Defaults to "App" for basic and "Generators" for generator commands.',
                acceptsValue: true,
            ));
    }

    protected function interact(array &$arguments, array &$options): void
    {
        $type = $this->getUnboundOption('type', $options);

        if (! is_string($type) || $type === 'basic' || $type === 'generator') {
            return;
        }

        $options['type'] = CLI::prompt(lang('CLI.generator.commandType'), ['basic', 'generator'], 'required');
    }

    protected function execute(array $arguments, array $options): int
    {
        $type = $this->getValidatedOption('type');

        if ($type !== 'basic' && $type !== 'generator') {
            CLI::error(lang('CLI.generator.invalidCommandType', [$type]));

            return EXIT_ERROR;
        }

        return $this->generateClass();
    }

    protected function getReplacements(string $class): array
    {
        $group = $this->getValidatedOption('group');

        if (! is_string($group)) {
            $group = $this->getValidatedOption('type') === 'generator' ? 'Generators' : 'App';
        }

        return ['{command}' => $this->getValidatedOption('command'), '{group}' => $group];
    }

    protected function getTemplateData(string $class): array
    {
        return ['type' => $this->getValidatedOption('type')];
    }
}
