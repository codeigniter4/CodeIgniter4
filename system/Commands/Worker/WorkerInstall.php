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
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Option;

/**
 * Installs the files needed to run CodeIgniter 4 in FrankenPHP worker mode.
 */
#[Command(
    name: 'worker:install',
    description: 'Install FrankenPHP worker mode by creating necessary configuration files',
    group: 'Worker Mode',
)]
class WorkerInstall extends AbstractCommand
{
    /**
     * Template file mappings (template => destination path).
     *
     * @var array<string, string>
     */
    private array $templates = [
        'frankenphp-worker.php.tpl' => 'public/frankenphp-worker.php',
        'Caddyfile.tpl'             => 'Caddyfile',
    ];

    protected function configure(): void
    {
        $this->addOption(new Option(
            name: 'force',
            shortcut: 'f',
            description: 'Overwrite existing files.',
        ));
    }

    protected function execute(array $arguments, array $options): int
    {
        $force = $options['force'] === true;

        CLI::write('Setting up FrankenPHP Worker Mode', 'yellow');
        CLI::newLine();

        helper('filesystem');

        $created = [];

        foreach ($this->templates as $template => $destination) {
            $source = SYSTEMPATH . 'Commands/Worker/Views/' . $template;
            $target = ROOTPATH . $destination;

            $isFile = is_file($target);

            if (! $force && $isFile) {
                continue;
            }

            $content = file_get_contents($source);

            if ($content === false) {
                CLI::error(sprintf('Failed to read template: %s', $template), 'light_gray', 'red');

                return EXIT_ERROR;
            }

            if (! write_file($target, $content)) {
                CLI::error(sprintf('Failed to create file: %s', clean_path($target)), 'light_gray', 'red');

                return EXIT_ERROR;
            }

            if ($force && $isFile) {
                CLI::write(sprintf('  File overwritten: %s', clean_path($target)), 'yellow');
            } else {
                CLI::write(sprintf('  File created: %s', clean_path($target)), 'green');
            }

            $created[] = $destination;
        }

        if ($created === []) {
            CLI::write('Worker mode files already exist.', 'yellow');
            CLI::write('Use --force to overwrite existing files.', 'yellow');

            return EXIT_ERROR;
        }

        CLI::newLine();
        CLI::write('Worker mode files created successfully!', 'green');
        CLI::newLine();

        $this->showNextSteps();

        return EXIT_SUCCESS;
    }

    private function showNextSteps(): void
    {
        CLI::write('Next Steps:', 'yellow');
        CLI::newLine();

        CLI::write('1. Start FrankenPHP:', 'white');
        CLI::write('   frankenphp run', 'green');
        CLI::newLine();

        CLI::write('2. Test your application:', 'white');
        CLI::write('   curl http://localhost:8080/', 'green');
    }
}
