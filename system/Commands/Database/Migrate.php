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
use Throwable;

/**
 * Runs all new migrations.
 */
class Migrate extends BaseCommand
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
    protected $name = 'migrate';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Locates and runs all new migrations against the database.';

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = 'migrate [options]';

    /**
     * the Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '-n'    => 'Set migration namespace',
        '-g'    => 'Set database group',
        '--all' => 'Set for all namespaces, will ignore (-n) option',
    ];

    /**
     * Ensures that all migrations have been run.
     */
    public function run(array $params)
    {
        $runner = Services::migrations();
        $runner->clearCliMessages();

        CLI::write(lang('Migrations.latest'), 'yellow');

        $namespace = $params['n'] ?? CLI::getOption('n');
        $group     = $params['g'] ?? CLI::getOption('g');

        try {
            if (array_key_exists('all', $params) || CLI::getOption('all')) {
                $runner->setNamespace(null);
            } elseif ($namespace) {
                $runner->setNamespace($namespace);
            }

            if (! $runner->latest($group)) {
                CLI::error(lang('Migrations.generalFault'), 'light_gray', 'red'); // @codeCoverageIgnore
            }

            $messages = $runner->getCliMessages();

            foreach ($messages as $message) {
                CLI::write($message);
            }

            CLI::write(lang('Migrations.migrated'), 'green');

            // @codeCoverageIgnoreStart
        } catch (Throwable $e) {
            $this->showError($e);
            // @codeCoverageIgnoreEnd
        }
    }
}
