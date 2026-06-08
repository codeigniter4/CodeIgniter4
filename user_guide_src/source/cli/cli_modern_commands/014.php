<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;

#[Command(
    name: 'app:deploy',
    description: 'Deploys the application.',
    group: 'App',
    aliases: ['app:ship', 'deploy'],
)]
class AppDeploy extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        // Reachable as `php spark app:deploy`, `php spark app:ship`, or `php spark deploy`.

        return EXIT_SUCCESS;
    }
}
