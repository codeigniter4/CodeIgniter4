<?php

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SampleCommand extends BaseCommand
{
    public function run(array $params): int
    {
        // Register signals with custom handlers
        $this->registerSignals(
            [SIGTERM, SIGINT, SIGUSR1, SIGUSR2],
            [
                SIGTERM => 'onGracefulShutdown',
                SIGINT  => 'onInterrupt',
                SIGUSR1 => 'onToggleDebug',
                SIGUSR2 => 'onStatusReport',
            ],
        );

        while ($this->isRunning()) {
            // Call custom method
            $this->doWork();
            sleep(1);
        }

        return EXIT_SUCCESS;
    }

    protected function onGracefulShutdown(int $signal): void
    {
        CLI::write('Received SIGTERM - shutting down gracefully...', 'yellow');
    }

    protected function onInterrupt(int $signal): void
    {
        CLI::write('Received SIGINT - stopping!', 'red');
    }

    protected function onToggleDebug(int $signal): void
    {
        // Custom debug mode
        $this->debugMode = ! $this->debugMode;
        CLI::write('Debug mode: ' . ($this->debugMode ? 'ON' : 'OFF'), 'blue');
    }

    protected function onStatusReport(int $signal): void
    {
        $state = $this->getProcessState();
        CLI::write('Status: ' . json_encode($state, JSON_PRETTY_PRINT), 'cyan');
    }
}
