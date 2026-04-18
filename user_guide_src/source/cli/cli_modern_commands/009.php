<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

class Publish extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'app:publish';
    protected $description = 'Publishes something.';
    protected $usage       = 'app:publish [<target>] [options]';
    protected $arguments   = ['target' => 'What to publish.'];
    protected $options     = [
        '--force'   => 'Overwrite existing output.',
        '--dry-run' => 'Print the plan without writing anything.',
    ];

    public function run(array $params)
    {
        try {
            $target = $params[0] ?? CLI::prompt('What should I publish?', null, 'required');

            $this->publish($target);

            return EXIT_SUCCESS;
        } catch (Throwable $e) {
            $this->showError($e);

            return EXIT_ERROR;
        }
    }

    private function publish(string $target): void
    {
        // Option values come from CLI's global state, so the sub-method still
        // has to thread the positional $target through but can reach the
        // named options without extra parameters.
        $force  = CLI::getOption('force') !== null;
        $dryRun = CLI::getOption('dry-run') !== null;

        // ...
        unset($force, $dryRun);

        CLI::write(sprintf('publishing %s', $target), 'green');
    }
}
