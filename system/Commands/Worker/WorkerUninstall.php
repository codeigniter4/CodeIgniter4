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

namespace CodeIgniter\Commands\Worker;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Uninstall Worker Mode for FrankenPHP.
 *
 * This command removes the files created by the worker:install command.
 */
class WorkerUninstall extends BaseCommand
{
    protected $group       = 'Worker Mode';
    protected $name        = 'worker:uninstall';
    protected $description = 'Remove FrankenPHP worker mode configuration files';
    protected $usage       = 'worker:uninstall [options]';
    protected $options     = [
        '--force' => 'Skip confirmation prompt',
    ];

    /**
     * Files to remove (must match Install command)
     *
     * @var list<string>
     */
    private array $files = [
        'public/frankenphp-worker.php',
        'Caddyfile',
    ];

    public function run(array $params)
    {
        $force = array_key_exists('force', $params) || CLI::getOption('force');

        CLI::write('Uninstalling FrankenPHP Worker Mode', 'yellow');
        CLI::newLine();

        // Find existing files
        $existing = [];

        foreach ($this->files as $file) {
            $path = ROOTPATH . $file;
            if (is_file($path)) {
                $existing[] = $file;
            }
        }

        // No files to remove
        if ($existing === []) {
            CLI::write('No worker mode files found to remove.', 'yellow');
            CLI::newLine();

            return EXIT_SUCCESS;
        }

        // Show files that will be removed
        CLI::write('The following files will be removed:', 'yellow');

        foreach ($existing as $file) {
            CLI::write('  - ' . $file, 'white');
        }
        CLI::newLine();

        // Confirm deletion unless --force is used
        if (! $force) {
            $confirm = CLI::prompt('Are you sure you want to remove these files?', ['y', 'n']);
            CLI::newLine();

            if ($confirm !== 'y') {
                CLI::write('Uninstall cancelled.', 'yellow');
                CLI::newLine();

                return EXIT_ERROR;
            }
        }

        $removed = [];

        // Remove each file
        foreach ($existing as $file) {
            $path = ROOTPATH . $file;

            if (! @unlink($path)) {
                CLI::error('Failed to remove file: ' . clean_path($path), 'light_gray', 'red');

                continue;
            }

            CLI::write('  File removed: ' . clean_path($path), 'green');
            $removed[] = $file;
        }

        // Summary
        CLI::newLine();
        if ($removed === []) {
            CLI::error('No files were removed.');
            CLI::newLine();

            return EXIT_ERROR;
        }

        CLI::write('Worker mode files removed successfully!', 'green');
        CLI::newLine();

        return EXIT_SUCCESS;
    }
}
