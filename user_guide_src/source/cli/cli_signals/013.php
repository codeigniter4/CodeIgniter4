<?php

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\SignalTrait;

class SampleCommand extends BaseCommand
{
    use SignalTrait;

    public function run(array $params): int
    {
        // ...
    }

    // Debug process state
    public function debugProcessState(): void
    {
        $state = $this->getProcessState();

        CLI::write('=== Process Debug Information ===');
        CLI::write("PID: {$state['pid']}");
        CLI::write('Running: ' . ($state['running'] ? 'Yes' : 'No'));
        CLI::write("Memory Usage: {$state['memory_usage_mb']} MB");
        CLI::write("Peak Memory: {$state['memory_peak_mb']} MB");
        CLI::write('Registered Signals: ' . implode(', ', $state['registered_signals_names']));
        CLI::write('Signals Blocked: ' . ($state['signals_blocked'] ? 'Yes' : 'No'));
    }
}
