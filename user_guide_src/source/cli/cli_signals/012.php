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

    // Log signal information
    public function onInterruption(int $signal): void
    {
        $signalName = $this->getSignalName($signal);
        CLI::write("Received signal: {$signalName} ({$signal})", 'yellow');
    }
}
