<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Displays a list of all migrations and whether they've been run or not.
 *
 * @see \CodeIgniter\Commands\Database\MigrateStatusTest
 */
class MigrateStatus extends BaseCommand
{
    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'Database';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'migrate:status';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Displays a list of all migrations and whether they\'ve been run or not.';

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = 'migrate:status [options]';

    /**
     * the Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '-g' => 'Set database group',
    ];

    /**
     * Namespaces to ignore when looking for migrations.
     *
     * @var string[]
     */
    protected $ignoredNamespaces = [
        'CodeIgniter',
        'Config',
        'Kint',
        'Laminas\ZendFrameworkBridge',
        'Laminas\Escaper',
        'Psr\Log',
    ];

    /**
     * Displays a list of all migrations and whether they've been run or not.
     *
     * @param array<string, mixed> $params
     */
    public function run(array $params)
    {
        $runner     = Services::migrations();
        $paramGroup = $params['g'] ?? CLI::getOption('g');

        // Get all namespaces
        $namespaces = Services::autoloader()->getNamespace();

        // Collection of migration status
        $status = [];

        foreach (array_keys($namespaces) as $namespace) {
            if (ENVIRONMENT !== 'testing') {
                // Make Tests\\Support discoverable for testing
                $this->ignoredNamespaces[] = 'Tests\Support'; // @codeCoverageIgnore
            }

            if (in_array($namespace, $this->ignoredNamespaces, true)) {
                continue;
            }

            if (APP_NAMESPACE !== 'App' && $namespace === 'App') {
                continue; // @codeCoverageIgnore
            }

            $migrations = $runner->findNamespaceMigrations($namespace);

            if (empty($migrations)) {
                continue;
            }

            $runner->setNamespace($namespace);
            $history = $runner->getHistory((string) $paramGroup);
            ksort($migrations);

            foreach ($migrations as $uid => $migration) {
                $migrations[$uid]->name = mb_substr($migration->name, mb_strpos($migration->name, $uid . '_'));

                $date  = '---';
                $group = '---';
                $batch = '---';

                foreach ($history as $row) {
                    // @codeCoverageIgnoreStart
                    if ($runner->getObjectUid($row) !== $migration->uid) {
                        continue;
                    }

                    $date  = date('Y-m-d H:i:s', $row->time);
                    $group = $row->group;
                    $batch = $row->batch;
                    // @codeCoverageIgnoreEnd
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

        if (! $status) {
            // @codeCoverageIgnoreStart
            CLI::error(lang('Migrations.noneFound'), 'light_gray', 'red');
            CLI::newLine();

            return;
            // @codeCoverageIgnoreEnd
        }

        $headers = [
            CLI::color(lang('Migrations.namespace'), 'yellow'),
            CLI::color(lang('Migrations.version'), 'yellow'),
            CLI::color(lang('Migrations.filename'), 'yellow'),
            CLI::color(lang('Migrations.group'), 'yellow'),
            CLI::color(str_replace(': ', '', lang('Migrations.on')), 'yellow'),
            CLI::color(lang('Migrations.batch'), 'yellow'),
        ];

        CLI::table($status, $headers);
    }
}
