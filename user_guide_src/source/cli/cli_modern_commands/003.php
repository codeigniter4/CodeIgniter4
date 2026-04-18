<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Input\Option;

#[Command(name: 'app:publish', description: 'Publishes assets.', group: 'App')]
class AppPublish extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            // Flag. Presence → true, absence → false.
            ->addOption(new Option(
                name: 'verbose',
                shortcut: 'v',
                description: 'Enable verbose output.',
            ))
            // Value-required. --destination=path or -d path. A string default is mandatory.
            ->addOption(new Option(
                name: 'destination',
                shortcut: 'd',
                description: 'Destination folder.',
                requiresValue: true,
                default: 'public',
            ))
            // Value-optional. Both `--driver` and `--driver=redis` are accepted.
            ->addOption(new Option(
                name: 'driver',
                description: 'Optional driver override.',
                acceptsValue: true,
            ))
            // Array. `--tag=a --tag=b` collects to ['a', 'b']. Array options must require a value.
            ->addOption(new Option(
                name: 'tag',
                shortcut: 't',
                description: 'Tag to publish (may be repeated).',
                requiresValue: true,
                isArray: true,
            ))
            // Negatable. Both --clean and --no-clean are registered automatically.
            ->addOption(new Option(
                name: 'clean',
                description: 'Clean the destination first.',
                negatable: true,
                default: true,
            ));
    }

    protected function execute(array $arguments, array $options): int
    {
        // ...

        return EXIT_SUCCESS;
    }
}
