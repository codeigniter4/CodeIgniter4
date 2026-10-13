<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Table;

#[Command(name: 'app:report', description: 'Prints a report.', group: 'App')]
class AppReport extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        $output = $this->getOutput();

        $output->write('Generating the report...', 'yellow');

        $rows = $this->collectRows();

        if ($rows === []) {
            $output->error('Nothing to report.');

            return EXIT_ERROR;
        }

        $output->write((new Table($rows, ['ID', 'Title']))->render());
        $output->write(sprintf('%d rows.', count($rows)), 'green');

        return EXIT_SUCCESS;
    }

    /**
     * @return list<array{int, string}>
     */
    private function collectRows(): array
    {
        return [[1, 'First'], [2, 'Second']];
    }
}
