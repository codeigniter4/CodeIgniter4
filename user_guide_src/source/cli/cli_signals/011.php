<?php

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\SignalTrait;

class SampleCommand extends BaseCommand
{
    use SignalTrait;
    public function run(array $params): int
    {
        // Register signals first
        $this->registerSignals([SIGTERM, SIGINT, SIGUSR1, SIGUSR2]);

        // Map signals to specific methods at runtime
        $this->mapSignal(SIGUSR1, 'handleReload');
        $this->mapSignal(SIGUSR2, 'handleStatusDump');
    }

    // Custom signal handlers
    public function handleReload(int $signal): void
    {
        CLI::write('Received reload signal, reloading configuration...');
        $this->reloadConfig();
    }

    public function handleStatusDump(int $signal): void
    {
        CLI::write('=== Process Status ===');
        $this->printStatus($this->getProcessState());
    }
}
