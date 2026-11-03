<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Input\Argument;

#[Command(name: 'app:greet', description: 'Greets people.', group: 'App')]
class AppGreet extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument(new Argument(name: 'name', required: true))
            // The default usage line is always generated from the declared
            // arguments; these extra lines are appended after it.
            ->addUsage('app:greet Alice')
            ->addUsage('app:greet "Bob the Builder"');
    }

    protected function execute(array $arguments, array $options): int
    {
        // ...

        return EXIT_SUCCESS;
    }
}
