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

/**
 * Does a rollback followed by a latest to refresh the current state of the database.
 */
#[Command(
    name: 'migrate:refresh',
    description: 'Does a rollback followed by a latest to refresh the current state of the database.',
    group: 'Database',
)]
class MigrateRefresh extends AbstractCommand
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
                description: 'Set latest for all namespaces. This will ignore the `--namespace` option.',
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

        if (CLI::prompt(lang('Migrations.refreshConfirm'), ['y', 'n']) === 'y') {
            $options['force'] = null; // simulate the presence of the --force option
        }
    }

    protected function execute(array $arguments, array $options): int
    {
        if (service('environment')->isProduction() && $options['force'] === false) {
            return EXIT_ERROR;
        }

        $namespace = $options['namespace'];
        assert(is_string($namespace));

        $group = $options['group'];
        assert(is_string($group));

        // A target batch of 0 rolls everything back before re-applying.
        $rollbackOptions = ['batch' => '0'];
        $migrateOptions  = [];

        if ($options['force'] === true) {
            $rollbackOptions['force'] = null;
        }

        if ($namespace !== '') {
            $migrateOptions['namespace'] = $namespace;
        }

        if ($group !== '') {
            $migrateOptions['group'] = $group;
        }

        if ($options['all'] === true) {
            $migrateOptions['all'] = null;
        }

        return $this->withSignalsBlocked(
            fn (): int => $this->call('migrate:rollback', options: $rollbackOptions)
                | $this->call('migrate', options: $migrateOptions),
        );
    }
}
