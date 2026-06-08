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
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\Database\Seeder;
use Config\Database;
use Throwable;

/**
 * Runs the specified seeder to populate known data into the database.
 */
#[Command(
    name: 'db:seed',
    description: 'Runs the specified seeder to populate known data into the database.',
    group: 'Database',
)]
class Seed extends AbstractCommand
{
    protected function configure(): void
    {
        $this->addArgument(new Argument(
            name: 'seeder_name',
            description: 'The seeder name to run.',
            required: true,
        ));
    }

    protected function interact(array &$arguments, array &$options): void
    {
        if ($arguments === []) {
            $arguments[] = CLI::prompt(lang('Migrations.migSeeder'), null, 'required');
        }
    }

    protected function execute(array $arguments, array $options): int
    {
        $seedName = $arguments['seeder_name'];
        assert(is_string($seedName));

        try {
            (new Seeder(new Database()))->call($seedName);

            return EXIT_SUCCESS;
        } catch (Throwable $e) {
            $this->renderThrowable($e);

            return EXIT_ERROR;
        }
    }
}
