<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\CLI;

#[Command(name: 'app:greet', description: 'Prints a greeting.', group: 'App')]
class AppGreet extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        CLI::write('Hello!', 'green');

        return EXIT_SUCCESS;
    }
}
