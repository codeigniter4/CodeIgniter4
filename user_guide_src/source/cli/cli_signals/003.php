<?php

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SampleCommand extends BaseCommand
{
    public function run(array $params): int
    {
        // Register signals without explicit mappings
        $this->registerSignals([SIGTERM, SIGINT, SIGHUP, SIGUSR1]);

        while ($this->isRunning()) {
            $this->doWork();
            sleep(1);
        }

        return EXIT_SUCCESS;
    }

    /**
     * Generic handler for all unmapped signals
     */
    protected function onInterruption(int $signal): void
    {
        $signalName = $this->getSignalName($signal);
        CLI::write("Received {$signalName} - handling generically", 'yellow');

        switch ($signal) {
            case SIGTERM:
                CLI::write('Graceful shutdown requested', 'green');
                break;

            case SIGINT:
                CLI::write('Immediate shutdown requested', 'red');
                break;

            case SIGHUP:
                CLI::write('Configuration reload requested', 'blue');
                break;

            case SIGUSR1:
                CLI::write('User signal 1 received', 'cyan');
                break;

            default:
                CLI::write('Unknown signal received', 'light_gray');
                break;
        }
    }
}
