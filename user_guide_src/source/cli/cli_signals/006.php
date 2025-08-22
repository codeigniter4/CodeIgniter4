<?php

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SampleCommand extends BaseCommand
{
    // ...

    protected function debugProcessState(): void
    {
        $state = $this->getProcessState();

        CLI::write('=== PROCESS DEBUG INFO ===', 'yellow');
        CLI::write('PID: ' . $state['pid'], 'cyan');
        CLI::write('Running: ' . ($state['running'] ? 'YES' : 'NO'), 'cyan');
        CLI::write('PCNTL Available: ' . ($state['pcntl_available'] ? 'YES' : 'NO'), 'cyan');
        CLI::write('Signals Registered: ' . $state['registered_signals'], 'cyan');
        CLI::write('Signal Names: ' . implode(', ', $state['registered_signals_names']), 'cyan');
        CLI::write('Explicit Mappings: ' . $state['explicit_mappings'], 'cyan');
        CLI::write('Signals Blocked: ' . ($state['signals_blocked'] ? 'YES' : 'NO'), 'cyan');
        CLI::write('Memory Usage: ' . $state['memory_usage_mb'] . ' MB', 'cyan');
        CLI::write('Peak Memory: ' . $state['memory_peak_mb'] . ' MB', 'cyan');

        // POSIX info (if available)
        if (isset($state['session_id'])) {
            CLI::write('Session ID: ' . $state['session_id'], 'cyan');
            CLI::write('Process Group: ' . $state['process_group'], 'cyan');
            CLI::write('Has Terminal: ' . ($state['has_controlling_terminal'] ? 'YES' : 'NO'), 'cyan');
        }

        CLI::write('========================', 'yellow');
    }
}
