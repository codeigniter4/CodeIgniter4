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

#[Command(name: 'make:model', description: 'Generates a new model file.', group: 'Generators')]
#[GeneratorCommand(
    component: 'Model',
    template: 'model.tpl.php',
    directory: 'Models',
    classNameLang: 'CLI.generator.className.model',
)]
class ModelGenerator extends AbstractGeneratorCommand
{
    private const RETURN_TYPES = ['array', 'object', 'entity'];

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption(new Option(
                name: 'table',
                shortcut: 't',
                description: 'Table name. Defaults to the lowercased plural of the class name.',
                acceptsValue: true,
                valueLabel: 'name',
            ))
            ->addOption(new Option(
                name: 'dbgroup',
                shortcut: 'g',
                description: 'Database group to use.',
                acceptsValue: true,
                valueLabel: 'group',
            ))
            ->addOption(new Option(
                name: 'return',
                shortcut: 'r',
                description: 'Return type: "array", "object", or "entity".',
                requiresValue: true,
                valueLabel: 'type',
                default: 'array',
            ));
    }

    protected function interact(array &$arguments, array &$options): void
    {
        $return = $this->getUnboundOption('return', $options);

        if (! is_string($return) || in_array($return, self::RETURN_TYPES, true)) {
            return;
        }

        $options['return'] = CLI::prompt(lang('CLI.generator.returnType'), self::RETURN_TYPES, 'required');
    }

    protected function execute(array $arguments, array $options): int
    {
        $return = $this->getValidatedOption('return');

        if (! in_array($return, self::RETURN_TYPES, true)) {
            CLI::error(lang('CLI.generator.invalidReturnType', [$return]));

            return EXIT_ERROR;
        }

        $exitCode = $this->generateClass();

        if ($exitCode !== EXIT_SUCCESS || $return !== 'entity') {
            return $exitCode;
        }

        $entityOptions = ['namespace' => $this->getValidatedOption('namespace')];

        if ($this->getValidatedOption('force') === true) {
            $entityOptions['force'] = null;
        }

        return $this->call('make:entity', [$this->getEntityClass($this->qualifyClassName())], $entityOptions);
    }

    protected function getReplacements(string $class): array
    {
        $table   = $this->getValidatedOption('table');
        $dbGroup = $this->getValidatedOption('dbgroup');

        $return = $this->getValidatedOption('return') === 'entity'
            ? '\\' . $this->getEntityClass($class) . '::class'
            : sprintf("'%s'", $this->getValidatedOption('return'));

        return [
            '{dbGroup}' => is_string($dbGroup) ? $dbGroup : '',
            '{table}'   => is_string($table) ? $table : plural(strtolower($this->stripModelSuffix(class_basename($class)))),
            '{return}'  => $return,
        ];
    }

    protected function getTemplateData(string $class): array
    {
        return ['dbGroup' => $this->getValidatedOption('dbgroup')];
    }

    /**
     * Derives the entity class from the qualified model class, keeping any sub-namespace.
     */
    private function getEntityClass(string $class): string
    {
        $entity = $this->stripModelSuffix(str_replace('\\Models\\', '\\Entities\\', $class));

        return $this->shouldAppendSuffix() ? $entity . 'Entity' : $entity;
    }

    private function stripModelSuffix(string $class): string
    {
        return preg_replace('/^(.+)Model$/i', '$1', $class) ?? $class;
    }
}
