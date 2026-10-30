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
use CodeIgniter\CLI\Output;

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
        $force  = $options['force'] === true;
        $output = $this->getOutput();

        $output->write('Setting up FrankenPHP Worker Mode', 'yellow');
        $output->newLine();

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
                $output->error(sprintf('Failed to read template: %s', $template), 'light_gray', 'red');

                return EXIT_ERROR;
            }

            if (! write_file($target, $content)) {
                $output->error(sprintf('Failed to create file: %s', clean_path($target)), 'light_gray', 'red');

                return EXIT_ERROR;
            }

            if ($force && $isFile) {
                $output->write(sprintf('  File overwritten: %s', clean_path($target)), 'yellow');
            } else {
                $output->write(sprintf('  File created: %s', clean_path($target)), 'green');
            }

            $created[] = $destination;
        }

        if ($created === []) {
            $output->write('Worker mode files already exist.', 'yellow');
            $output->write('Use --force to overwrite existing files.', 'yellow');

            return EXIT_ERROR;
        }

        $output->newLine();
        $output->write('Worker mode files created successfully!', 'green');
        $output->newLine();

        $this->showNextSteps($output);

        return EXIT_SUCCESS;
    }

    private function showNextSteps(Output $output): void
    {
        $output->write('Next Steps:', 'yellow');
        $output->newLine();

        $output->write('1. Start FrankenPHP:', 'white');
        $output->write('   frankenphp run', 'green');
        $output->newLine();

        $output->write('2. Test your application:', 'white');
        $output->write('   curl http://localhost:8080/', 'green');
    }
}
