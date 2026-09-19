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

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\CLI\Input\Option;
use CodeIgniter\CLI\PromptsForMissingInputInterface;

#[Command(name: 'make:scaffold', description: 'Generates a complete set of scaffold files.', group: 'Generators')]
class ScaffoldGenerator extends AbstractCommand implements PromptsForMissingInputInterface
{
    protected function configure(): void
    {
        $this
            ->addArgument(new Argument(name: 'name', description: 'The class name.', required: true))
            ->addOption(new Option(
                name: 'bare',
                shortcut: 'b',
                description: 'Pass "--bare" to the controller.',
            ))
            ->addOption(new Option(
                name: 'restful',
                description: 'Pass "--restful" to the controller.',
                acceptsValue: true,
                valueLabel: 'type',
            ))
            ->addOption(new Option(
                name: 'table',
                shortcut: 't',
                description: 'Pass "--table" to the model.',
                acceptsValue: true,
                valueLabel: 'name',
            ))
            ->addOption(new Option(
                name: 'dbgroup',
                shortcut: 'g',
                description: 'Pass "--dbgroup" to the model.',
                acceptsValue: true,
                valueLabel: 'group',
            ))
            ->addOption(new Option(
                name: 'return',
                description: 'Pass "--return" to the model.',
                acceptsValue: true,
                valueLabel: 'type',
            ))
            ->addOption(new Option(
                name: 'namespace',
                shortcut: 'n',
                description: 'Set the root namespace.',
                requiresValue: true,
                default: APP_NAMESPACE,
            ))
            ->addOption(new Option(
                name: 'suffix',
                shortcut: 's',
                description: 'Append the component suffix to each class name.',
            ))
            ->addOption(new Option(
                name: 'force',
                shortcut: 'f',
                description: 'Force overwrite existing files.',
            ));
    }

    protected function getArgumentPromptLabels(): array
    {
        return ['name' => lang('CLI.generator.className.default')];
    }

    protected function execute(array $arguments, array $options): int
    {
        $name   = [$arguments['name']];
        $shared = ['namespace' => $options['namespace']];

        if ($options['suffix'] === true) {
            $shared['suffix'] = null;
        }

        $forced = $options['force'] === true ? $shared + ['force' => null] : $shared;

        return $this->call('make:controller', $name, $this->getControllerOptions($options) + $forced)
            | $this->call('make:model', $name, $this->getModelOptions($options) + $forced)
            | $this->call('make:migration', $name, $shared)
            | $this->call('make:seeder', $name, $forced);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, string|null>
     */
    private function getControllerOptions(array $options): array
    {
        if ($options['bare'] === true) {
            return ['bare' => null];
        }

        if (! $this->hasUnboundOption('restful')) {
            return [];
        }

        return ['restful' => is_string($options['restful']) ? $options['restful'] : null];
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, string>
     */
    private function getModelOptions(array $options): array
    {
        return array_filter([
            'table'   => $options['table'],
            'dbgroup' => $options['dbgroup'],
            'return'  => $options['return'],
        ], is_string(...));
    }
}
