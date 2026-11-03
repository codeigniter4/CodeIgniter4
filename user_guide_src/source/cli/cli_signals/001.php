<?php

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SampleCommand extends BaseCommand
{
    public function run(array $params): int
    {
        // Register basic termination signals
        $this->registerSignals();

        // Main processing loop
        while ($this->isRunning()) {
            // Do work here
            $this->processItem();

            sleep(3);
        }

        CLI::write('Command terminated gracefully', 'green');

        return EXIT_SUCCESS;
    }
}
