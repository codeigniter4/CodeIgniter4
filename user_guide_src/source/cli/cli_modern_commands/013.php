<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;

#[Command(name: 'dev:only', description: 'A dev only command', group: 'Dev')]
class DevOnly extends AbstractCommand
{
    protected function isAvailable(): bool
    {
        // Only allow this command in the development environment
        return ENVIRONMENT === 'development';
    }

    protected function execute(array $arguments, array $options): int
    {
        // ...

        return EXIT_SUCCESS;
    }
}
