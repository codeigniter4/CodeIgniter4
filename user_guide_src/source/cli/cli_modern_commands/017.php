<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\CLI;

#[Command(
    name: 'app:status',
    description: 'Prints the application status as JSON.',
    group: 'App',
    headerless: true,
)]
class AppStatus extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        CLI::write(json_encode(['status' => 'ok'], JSON_THROW_ON_ERROR));

        return EXIT_SUCCESS;
    }
}
