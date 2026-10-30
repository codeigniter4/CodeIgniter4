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

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Input\Option;

/**
 * Removes the files created by the worker:install command.
 */
#[Command(
    name: 'worker:uninstall',
    description: 'Remove FrankenPHP worker mode configuration files',
    group: 'Worker Mode',
)]
class WorkerUninstall extends AbstractCommand
{
    /**
     * Files to remove (must match the worker:install command).
     *
     * @var list<string>
     */
    private array $files = [
        'public/frankenphp-worker.php',
        'Caddyfile',
    ];

    protected function configure(): void
    {
        $this->addOption(new Option(
            name: 'force',
            shortcut: 'f',
            description: 'Skip the confirmation prompt.',
        ));
    }

    protected function interact(array &$arguments, array &$options): void
    {
        if ($this->hasUnboundOption('force', $options)) {
            return;
        }

        if ($this->existingFiles() === []) {
            return;
        }

        if ($this->getPrompt()->ask('Remove the FrankenPHP worker mode files?', ['y', 'n']) === 'y') {
            $options['force'] = null; // simulate the presence of the --force option
        }
    }

    protected function execute(array $arguments, array $options): int
    {
        $existing = $this->existingFiles();
        $output   = $this->getOutput();

        if ($existing === []) {
            $output->write('No worker mode files found to remove.', 'yellow');

            return EXIT_SUCCESS;
        }

        if ($options['force'] === false) {
            if ($this->isInteractive()) {
                $output->write('Uninstall cancelled.', 'yellow');

                return EXIT_SUCCESS;
            }

            $output->error('Uninstall aborted: pass --force to remove worker mode files in non-interactive mode.', 'light_gray', 'red');

            return EXIT_ERROR;
        }

        $output->newLine();
        $output->write('The following files will be removed:', 'yellow');

        foreach ($existing as $file) {
            $output->write(sprintf('  - %s', $file), 'white');
        }

        $output->newLine();

        $removed = [];

        foreach ($existing as $file) {
            $path = ROOTPATH . $file;

            if (! @unlink($path)) {
                $output->error(sprintf('Failed to remove file: %s', clean_path($path)), 'light_gray', 'red');

                continue;
            }

            $output->write(sprintf('  File removed: %s', clean_path($path)), 'green');

            $removed[] = $file;
        }

        $output->newLine();

        if ($removed === []) {
            $output->error('No files were removed.', 'light_gray', 'red');

            return EXIT_ERROR;
        }

        $output->write('Worker mode files removed successfully!', 'green');

        return EXIT_SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function existingFiles(): array
    {
        $existing = [];

        foreach ($this->files as $file) {
            if (is_file(ROOTPATH . $file)) {
                $existing[] = $file;
            }
        }

        return $existing;
    }
}
