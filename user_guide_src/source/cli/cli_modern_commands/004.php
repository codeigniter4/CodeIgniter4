<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Option;

#[Command(name: 'logs:clear', description: 'Clears logs.', group: 'Housekeeping')]
class ClearLogs extends AbstractCommand
{
    protected function configure(): void
    {
        $this->addOption(new Option(
            name: 'force',
            shortcut: 'f',
            description: 'Skip the confirmation prompt.',
        ));
    }

    protected function interact(array &$arguments, array &$options): void
    {
        // hasUnboundOption() resolves --force, -f, and --no-force in one call,
        // even though $options here is still the raw parsed input.
        if ($this->hasUnboundOption('force', $options)) {
            return;
        }

        if (CLI::prompt('Delete the logs?', ['n', 'y']) === 'n') {
            return;
        }

        // Mutations made here flow through to bind(), validate(), and execute().
        // For a flag option, writing `null` models "the flag was passed".
        $options['force'] = null;
    }

    protected function execute(array $arguments, array $options): int
    {
        if ($this->getValidatedOption('force') === false) {
            CLI::error('Aborted.');

            return EXIT_ERROR;
        }

        // ... actually delete the logs ...

        return EXIT_SUCCESS;
    }
}
