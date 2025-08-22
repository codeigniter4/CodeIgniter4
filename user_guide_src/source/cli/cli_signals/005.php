<?php

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;

class SampleCommand extends BaseCommand
{
    public function run(array $params): int
    {
        // Register pause/resume signals with custom handlers
        $this->registerSignals(
            [SIGTERM, SIGINT, SIGTSTP, SIGCONT],
            [
                SIGTSTP => 'onPause',
                SIGCONT => 'onResume',
            ],
        );

        while ($this->isRunning()) {
            $this->processWork();
            sleep(2);
        }

        return EXIT_SUCCESS;
    }

    protected function onPause(int $signal): void
    {
        CLI::write('Pausing - saving current date...', 'yellow');

        // Save current timestamp
        $state = [
            'timestamp' => Time::now()->getTimestamp(),
        ];

        file_put_contents(WRITEPATH . 'app_state.json', json_encode($state));

        CLI::write('State saved. Process will now suspend.', 'green');
    }

    protected function onResume(int $signal): void
    {
        CLI::write('Resuming - restoring...', 'green');

        $file = WRITEPATH . 'app_state.json';

        // Restore saved state
        if (file_exists($file)) {
            $state = json_decode(file_get_contents($file), true);
            $date  = Time::createFromTimestamp($state['timestamp'])->format('Y-m-d H:i:s');

            CLI::write('Restored from ' . $date, 'cyan');
        }

        CLI::write('Resuming normal operation...', 'green');
    }
}
