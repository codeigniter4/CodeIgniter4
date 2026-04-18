<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\CLI\Input\Option;

#[Command(name: 'app:demo', description: 'Demonstrates accessors.', group: 'App')]
class AppDemo extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument(new Argument(name: 'name', required: true))
            ->addOption(new Option(name: 'loud', negatable: true, default: false));
    }

    protected function execute(array $arguments, array $options): int
    {
        // Directly from the parameters:
        $name = $arguments['name'];

        // Or via the validated accessors — throws LogicException if the name
        // is not declared on this command:
        $name = $this->getValidatedArgument('name');
        $loud = $this->getValidatedOption('loud');

        // Need to know whether --loud was actually passed, not just whether it
        // resolved to its declared default? Use the unbound accessors:
        $loudWasPassed = $this->hasUnboundOption('loud');

        // ...

        return EXIT_SUCCESS;
    }
}
