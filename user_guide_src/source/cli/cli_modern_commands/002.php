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
            // Required, no default — the runner rejects invocations that omit it.
            ->addArgument(new Argument(
                name: 'name',
                description: 'Who to greet.',
                required: true,
            ))
            // Optional — a default is mandatory.
            ->addArgument(new Argument(
                name: 'salutation',
                description: 'Optional salutation.',
                default: 'Hello',
            ))
            // Array — collects every remaining token. Must be declared last.
            ->addArgument(new Argument(
                name: 'extras',
                description: 'Any extra tokens.',
                isArray: true,
            ));
    }

    protected function execute(array $arguments, array $options): int
    {
        // ...

        return EXIT_SUCCESS;
    }
}
