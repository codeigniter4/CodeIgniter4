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
 * Install Worker Mode for FrankenPHP.
 *
 * This command sets up the necessary files to run CodeIgniter 4
 * in FrankenPHP worker mode for improved performance.
 */
class WorkerInstall extends BaseCommand
{
    protected $group       = 'Worker Mode';
    protected $name        = 'worker:install';
    protected $description = 'Install FrankenPHP worker mode by creating necessary configuration files';
    protected $usage       = 'worker:install [options]';
    protected $options     = [
        '--force' => 'Overwrite existing files',
    ];

    /**
     * Template file mappings (template => destination path)
     *
     * @var array<string, string>
     */
    private array $templates = [
        'frankenphp-worker.php.tpl' => 'public/frankenphp-worker.php',
        'Caddyfile.tpl'             => 'Caddyfile',
    ];

    public function run(array $params)
    {
        $force = array_key_exists('force', $params) || CLI::getOption('force');

        CLI::write('Setting up FrankenPHP Worker Mode', 'yellow');
        CLI::newLine();

        helper('filesystem');

        $created = [];

        // Process each template
        foreach ($this->templates as $template => $destination) {
            $source = SYSTEMPATH . 'Commands/Worker/Views/' . $template;
            $target = ROOTPATH . $destination;

            $isFile = is_file($target);

            // Skip if file exists and not forcing overwrite
            if (! $force && $isFile) {
                continue;
            }

            // Read template content
            $content = file_get_contents($source);
            if ($content === false) {
                CLI::error(
                    "Failed to read template: {$template}",
                    'light_gray',
                    'red',
                );
                CLI::newLine();

                return EXIT_ERROR;
            }

            // Write file to destination
            if (! write_file($target, $content)) {
                CLI::error(
                    'Failed to create file: ' . clean_path($target),
                    'light_gray',
                    'red',
                );
                CLI::newLine();

                return EXIT_ERROR;
            }

            if ($force && $isFile) {
                CLI::write('  File overwritten: ' . clean_path($target), 'yellow');
            } else {
                CLI::write('  File created: ' . clean_path($target), 'green');
            }

            $created[] = $destination;
        }

        // No files were created
        if ($created === []) {
            CLI::newLine();
            CLI::write('Worker mode files already exist.', 'yellow');
            CLI::write('Use --force to overwrite existing files.', 'yellow');
            CLI::newLine();

            return EXIT_ERROR;
        }

        // Success message
        CLI::newLine();
        CLI::write('Worker mode files created successfully!', 'green');
        CLI::newLine();

        $this->showNextSteps();

        return EXIT_SUCCESS;
    }

    /**
     * Display next steps to the user
     */
    protected function showNextSteps(): void
    {
        CLI::write('Next Steps:', 'yellow');
        CLI::newLine();

        CLI::write('1. Start FrankenPHP:', 'white');
        CLI::write('   frankenphp run', 'green');
        CLI::newLine();

        CLI::write('2. Test your application:', 'white');
        CLI::write('   curl http://localhost:8080/', 'green');
        CLI::newLine();
    }
}
