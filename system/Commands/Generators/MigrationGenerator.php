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
use Config\Database;
use Config\Migrations;
use Config\Session as SessionConfig;

#[Command(name: 'make:migration', description: 'Generates a new migration file.', group: 'Generators')]
#[GeneratorCommand(
    component: 'Migration',
    template: 'migration.tpl.php',
    directory: 'Database\Migrations',
    classNameLang: 'CLI.generator.className.migration',
)]
class MigrationGenerator extends AbstractGeneratorCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption(new Option(
                name: 'session',
                description: 'Generate the migration file for database sessions.',
            ))
            ->addOption(new Option(
                name: 'table',
                shortcut: 't',
                description: 'Table name to use for database sessions.',
                requiresValue: true,
                default: 'ci_sessions',
            ))
            ->addOption(new Option(
                name: 'dbgroup',
                shortcut: 'g',
                description: 'Database group to use for database sessions.',
                requiresValue: true,
                valueLabel: 'group',
                default: 'default',
            ));
    }

    protected function provideGeneratorOptions(): void
    {
        $this->addNamespaceOption()->addSuffixOption();
    }

    protected function initialize(array &$arguments, array &$options): void
    {
        if (! $this->hasUnboundOption('session', $options)) {
            return;
        }

        $table = $this->getUnboundOption('table', $options);

        $arguments[0] = sprintf('_create_%s_table', is_string($table) ? $table : 'ci_sessions');
    }

    protected function execute(array $arguments, array $options): int
    {
        if ($this->getValidatedOption('session') === true) {
            $group  = $this->getDatabaseGroup();
            $driver = $this->getDatabaseDriver($group);

            if ($driver === null) {
                CLI::error(lang('CLI.generator.undefinedDatabaseGroup', [$group]));

                return EXIT_ERROR;
            }

            if ($driver !== 'MySQLi' && $driver !== 'Postgre') {
                CLI::error(lang('CLI.generator.unsupportedSessionDriver', [$group, $driver]));

                return EXIT_ERROR;
            }
        }

        return $this->generateClass();
    }

    protected function getTemplateData(string $class): array
    {
        if ($this->getValidatedOption('session') !== true) {
            return ['session' => false];
        }

        $group = $this->getDatabaseGroup();

        return [
            'session'  => true,
            'table'    => $this->getValidatedOption('table'),
            'DBGroup'  => $group,
            'DBDriver' => $this->getDatabaseDriver($group),
            'matchIP'  => config(SessionConfig::class)->matchIP,
        ];
    }

    protected function basename(string $filename): string
    {
        return gmdate(config(Migrations::class)->timestampFormat) . basename($filename);
    }

    private function getDatabaseGroup(): string
    {
        $group = $this->getValidatedOption('dbgroup');
        assert(is_string($group));

        return $group;
    }

    private function getDatabaseDriver(string $group): ?string
    {
        return config(Database::class)->{$group}['DBDriver'] ?? null;
    }
}
