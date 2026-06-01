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
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Option;
use CodeIgniter\CLI\SignalTrait;
use CodeIgniter\Database\MigrationRunner;
use Throwable;

/**
 * Runs the "down" method for all migrations in the last batch.
 */
#[Command(
    name: 'migrate:rollback',
    description: 'Runs the "down" method for all migrations in the last batch.',
    group: 'Database',
)]
class MigrateRollback extends AbstractCommand
{
    use SignalTrait;

    protected function configure(): void
    {
        $this
            ->addOption(new Option(
                name: 'batch',
                shortcut: 'b',
                description: 'Specify a batch to roll back to.',
                requiresValue: true,
                default: '',
            ))
            ->addOption(new Option(
                name: 'force',
                shortcut: 'f',
                description: 'Bypass the confirmation question when running this command in a production environment.',
            ));
    }

    protected function interact(array &$arguments, array &$options): void
    {
        if (! service('environment')->isProduction()) {
            return;
        }

        if ($this->hasUnboundOption('force', $options)) {
            return;
        }

        if (CLI::prompt(lang('Migrations.rollBackConfirm'), ['y', 'n']) === 'y') {
            $options['force'] = null; // simulate the presence of the --force option
        }
    }

    protected function execute(array $arguments, array $options): int
    {
        if (service('environment')->isProduction() && $options['force'] === false) {
            return EXIT_ERROR;
        }

        /** @var MigrationRunner $runner */
        $runner = service('migrations');

        try {
            $batch = $options['batch'];
            assert(is_string($batch));

            if ($batch === '') {
                $batch = $runner->getLastBatch() - 1;
            } elseif (! ctype_digit($batch)) {
                CLI::error('Invalid batch number: ' . $batch, 'light_gray', 'red');

                return EXIT_ERROR;
            } else {
                $batch = (int) $batch;
            }

            CLI::write(lang('Migrations.rollingBack') . ' ' . $batch, 'yellow');

            $exit = $this->withSignalsBlocked(static function () use ($runner, $batch): int {
                if (! $runner->regress($batch)) {
                    CLI::error(lang('Migrations.generalFault'), 'light_gray', 'red');

                    return EXIT_ERROR;
                }

                return EXIT_SUCCESS;
            });

            if ($exit !== EXIT_SUCCESS) {
                return $exit;
            }

            $messages = $runner->getCliMessages();

            foreach ($messages as $message) {
                CLI::write($message);
            }

            CLI::write('Done rolling back migrations.', 'green');

            return EXIT_SUCCESS;
        } catch (Throwable $e) {
            $this->renderThrowable($e);

            return EXIT_ERROR;
        }
    }
}
