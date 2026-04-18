<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\CLI\Input\Option;
use Throwable;

#[Command(name: 'app:publish', description: 'Publishes something.', group: 'App')]
class Publish extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument(new Argument(name: 'target', description: 'What to publish.', required: true))
            ->addOption(new Option(name: 'force', description: 'Overwrite existing output.'))
            ->addOption(new Option(name: 'dry-run', description: 'Print the plan without writing anything.'));
    }

    protected function interact(array &$arguments, array &$options): void
    {
        if ($arguments === []) {
            $arguments[] = CLI::prompt('What should I publish?', null, 'required');
        }
    }

    protected function execute(array $arguments, array $options): int
    {
        try {
            $this->publish($arguments['target']);

            return EXIT_SUCCESS;
        } catch (Throwable $e) {
            $this->renderThrowable($e);

            return EXIT_ERROR;
        }
    }

    private function publish(string $target): void
    {
        // Unlike the legacy version, the sub-method can reach the validated
        // options through the helpers without threading $options through.
        $force  = $this->getValidatedOption('force') === true;
        $dryRun = $this->getValidatedOption('dry-run') === true;

        // ...
        unset($force, $dryRun);

        CLI::write(sprintf('publishing %s', $target), 'green');
    }
}
