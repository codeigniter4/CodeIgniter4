<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;

#[Command(name: 'app:greet', description: 'Prints a greeting.', group: 'App')]
class AppGreet extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        $this->getOutput()->write('Hello!', 'green');

        return EXIT_SUCCESS;
    }
}
