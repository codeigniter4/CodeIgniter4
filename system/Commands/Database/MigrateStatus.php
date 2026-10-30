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

namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Input\Option;
use CodeIgniter\CLI\Table;
use CodeIgniter\Database\MigrationRunner;

/**
 * Displays a list of all migrations and whether they've been run or not.
 */
#[Command(
    name: 'migrate:status',
    description: 'Displays a list of all migrations and whether they\'ve been run or not.',
    group: 'Database',
)]
class MigrateStatus extends AbstractCommand
{
    /**
     * Namespaces to ignore when looking for migrations.
     *
     * @var list<string>
     */
    protected array $ignoredNamespaces = [
        'CodeIgniter',
        'Config',
        'Kint',
        'Laminas\ZendFrameworkBridge',
        'Laminas\Escaper',
        'Psr\Log',
    ];

    protected function configure(): void
    {
        $this->addOption(new Option(
            name: 'group',
            shortcut: 'g',
            description: 'Set database group.',
            requiresValue: true,
            default: '',
        ));
    }

    protected function execute(array $arguments, array $options): int
    {
        /** @var MigrationRunner $runner */
        $runner = service('migrations');

        $groupOption = $options['group'];
        assert(is_string($groupOption));

        $namespaces = service('autoloader')->getNamespace();

        $status = [];

        foreach (array_keys($namespaces) as $namespace) {
            if (! service('environment')->isTesting()) {
                // Make Tests\\Support discoverable for testing
                $this->ignoredNamespaces[] = 'Tests\Support';
            }

            if (in_array($namespace, $this->ignoredNamespaces, true)) {
                continue;
            }

            if (APP_NAMESPACE !== 'App' && $namespace === 'App') {
                continue; // @codeCoverageIgnore
            }

            $migrations = $runner->findNamespaceMigrations($namespace);

            if ($migrations === []) {
                continue;
            }

            $runner->setNamespace($namespace);
            $history = $runner->getHistory($groupOption);
            ksort($migrations);

            foreach ($migrations as $uid => $migration) {
                $migrations[$uid]->name = mb_substr($migration->name, (int) mb_strpos($migration->name, $uid . '_'));

                $date  = '---';
                $group = '---';
                $batch = '---';

                foreach ($history as $row) {
                    if ($runner->getObjectUid($row) !== $migration->uid) {
                        continue;
                    }

                    $date  = date('Y-m-d H:i:s', (int) $row->time);
                    $group = $row->group;
                    $batch = $row->batch;
                }

                $status[] = [
                    $namespace,
                    $migration->version,
                    $migration->name,
                    $group,
                    $date,
                    $batch,
                ];
            }
        }

        $output = $this->getOutput();

        if ($status === []) {
            $output->error(lang('Migrations.noneFound'), 'light_gray', 'red');

            return EXIT_ERROR;
        }

        $headers = [
            $output->color(lang('Migrations.namespace'), 'yellow'),
            $output->color(lang('Migrations.version'), 'yellow'),
            $output->color(lang('Migrations.filename'), 'yellow'),
            $output->color(lang('Migrations.group'), 'yellow'),
            $output->color(str_replace(': ', '', lang('Migrations.on')), 'yellow'),
            $output->color(lang('Migrations.batch'), 'yellow'),
        ];

        $output->write((new Table($status, $headers))->render());

        return EXIT_SUCCESS;
    }
}
