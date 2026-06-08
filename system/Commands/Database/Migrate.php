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
 * Locates and runs all new migrations against the database.
 */
#[Command(
    name: 'migrate',
    description: 'Locates and runs all new migrations against the database.',
    group: 'Database',
)]
class Migrate extends AbstractCommand
{
    use SignalTrait;

    protected function configure(): void
    {
        $this
            ->addOption(new Option(
                name: 'namespace',
                shortcut: 'n',
                description: 'Set migration namespace.',
                requiresValue: true,
                default: '',
            ))
            ->addOption(new Option(
                name: 'group',
                shortcut: 'g',
                description: 'Set database group.',
                requiresValue: true,
                default: '',
            ))
            ->addOption(new Option(
                name: 'all',
                description: 'Set for all namespaces. This will ignore the `--namespace` option.',
            ));
    }

    protected function execute(array $arguments, array $options): int
    {
        /** @var MigrationRunner $runner */
        $runner = service('migrations');
        $runner->clearCliMessages();

        CLI::write(lang('Migrations.latest'), 'yellow');

        $namespace = $options['namespace'];
        assert(is_string($namespace));

        $group = $options['group'];
        assert(is_string($group));

        $group = $group !== '' ? $group : null;

        try {
            if ($options['all'] === true) {
                $runner->setNamespace(null);
            } elseif ($namespace !== '') {
                $runner->setNamespace($namespace);
            }

            $this->withSignalsBlocked(static function () use ($runner, $group): void {
                if (! $runner->latest($group)) {
                    CLI::error(lang('Migrations.generalFault'), 'light_gray', 'red');
                }
            });

            $messages = $runner->getCliMessages();

            foreach ($messages as $message) {
                CLI::write($message);
            }

            CLI::write(lang('Migrations.migrated'), 'green');

            return EXIT_SUCCESS;
        } catch (Throwable $e) {
            $this->renderThrowable($e);

            return EXIT_ERROR;
        }
    }
}
