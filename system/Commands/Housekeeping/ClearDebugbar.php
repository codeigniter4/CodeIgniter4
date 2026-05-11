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

/**
 * Clears all debugbar JSON files.
 */
#[Command(name: 'debugbar:clear', description: 'Clears all debugbar JSON files.', group: 'Housekeeping')]
class ClearDebugbar extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        helper('filesystem');

        $path = clean_path(WRITEPATH . 'debugbar');

        if (! delete_files(WRITEPATH . 'debugbar', htdocs: true)) {
            CLI::error(sprintf('Error deleting the debugbar JSON files in "%s".', $path));

            return EXIT_ERROR;
        }

        CLI::write(sprintf('Cleared debugbar JSON files in "%s".', $path), 'green');

        return EXIT_SUCCESS;
    }
}
