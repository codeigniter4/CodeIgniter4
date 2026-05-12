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

namespace CodeIgniter\Commands\Housekeeping;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Option;

/**
 * Clears all log files.
 */
#[Command(name: 'logs:clear', description: 'Clears all log files.', group: 'Housekeeping')]
class ClearLogs extends AbstractCommand
{
    protected function configure(): void
    {
        $this->addOption(new Option(
            name: 'force',
            shortcut: 'f',
            description: 'Forces the clearing of log files without confirmation.',
        ));
    }

    protected function interact(array &$arguments, array &$options): void
    {
        if ($this->hasUnboundOption('force', $options)) {
            return;
        }

        if (CLI::prompt(sprintf('Delete all log files in %s?', clean_path(WRITEPATH . 'logs')), ['n', 'y']) === 'n') {
            return;
        }

        $options['force'] = null; // simulate the presence of the --force option
    }

    protected function execute(array $arguments, array $options): int
    {
        if ($options['force'] === false) {
            if ($this->isInteractive()) {
                CLI::write('Log deletion cancelled.', 'yellow');

                return EXIT_SUCCESS;
            }

            CLI::error('Log deletion aborted: pass --force to delete log files in non-interactive mode.');

            return EXIT_ERROR;
        }

        helper('filesystem');

        if (! delete_files(WRITEPATH . 'logs', htdocs: true)) {
            CLI::error(sprintf('Failed to delete log files in %s.', clean_path(WRITEPATH . 'logs')));

            return EXIT_ERROR;
        }

        CLI::write(sprintf('Log files in %s cleared.', clean_path(WRITEPATH . 'logs')), 'green');

        return EXIT_SUCCESS;
    }
}
