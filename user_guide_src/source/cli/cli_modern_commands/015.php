<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\CLI\PromptsForMissingInputInterface;

#[Command(name: 'app:greet', description: 'Greets people.', group: 'App')]
class AppGreet extends AbstractCommand implements PromptsForMissingInputInterface
{
    protected function configure(): void
    {
        $this->addArgument(new Argument(
            name: 'name',
            description: 'Who to greet.',
            required: true,
        ));
    }

    protected function getArgumentPromptLabels(): array
    {
        return ['name' => 'Who should be greeted?'];
    }

    protected function execute(array $arguments, array $options): int
    {
        CLI::write(sprintf('Hello, %s!', $arguments['name']), 'green');

        return EXIT_SUCCESS;
    }
}
