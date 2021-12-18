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
 * Runs all of the migrations in reverse order, until they have
 * all been unapplied.
 */
class MigrateRollback extends BaseCommand
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
    protected $name = 'migrate:rollback';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Runs the "down" method for all migrations in the last batch.';

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = 'migrate:rollback [options]';

    /**
     * the Command's Options
     *
     * @var array
     */
    protected $options = [
        '-b' => 'Specify a batch to roll back to; e.g. "3" to return to batch #3 or "-2" to roll back twice',
        '-g' => 'Set database group',
        '-f' => 'Force command - this option allows you to bypass the confirmation question when running this command in a production environment',
    ];

    /**
     * Runs all of the migrations in reverse order, until they have
     * all been unapplied.
     */
    public function run(array $params)
    {
        if (ENVIRONMENT === 'production') {
            // @codeCoverageIgnoreStart
            $force = array_key_exists('f', $params) || CLI::getOption('f');

            if (! $force && CLI::prompt(lang('Migrations.rollBackConfirm'), ['y', 'n']) === 'n') {
                return;
            }
            // @codeCoverageIgnoreEnd
        }

        $runner = Services::migrations();
        $group  = $params['g'] ?? CLI::getOption('g');

        if (is_string($group)) {
            $runner->setGroup($group);
        }

        try {
            $batch = $params['b'] ?? CLI::getOption('b') ?? $runner->getLastBatch() - 1;
            CLI::write(lang('Migrations.rollingBack') . ' ' . $batch, 'yellow');

            if (! $runner->regress($batch)) {
                CLI::error(lang('Migrations.generalFault'), 'light_gray', 'red'); // @codeCoverageIgnore
            }

            $messages = $runner->getCliMessages();

            foreach ($messages as $message) {
                CLI::write($message);
            }

            CLI::write('Done rolling back migrations.', 'green');

            // @codeCoverageIgnoreStart
        } catch (Throwable $e) {
            $this->showError($e);
            // @codeCoverageIgnoreEnd
        }
    }
}
