<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Table;
use CodeIgniter\CLI\Verbosity;

#[Command(name: 'app:sync', description: 'Synchronizes the records.', group: 'App')]
class AppSync extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        $output = $this->getOutput();
        $rows   = [[1, 'First'], [2, 'Second']];

        $output->write('Connecting to the remote...', verbosity: Verbosity::Verbose);
        $output->write(sprintf('%d records synchronized.', count($rows)), 'green');

        if ($output->isVeryVerbose()) {
            $output->write((new Table($rows, ['ID', 'Title']))->render());
        }

        return EXIT_SUCCESS;
    }
}
