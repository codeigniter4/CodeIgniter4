<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;

#[Command(
    name: 'app:reindex',
    description: 'Rebuilds the search index.',
    group: 'App',
    hidden: true,
)]
class AppReindex extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        // Not shown by `php spark list`, but `php spark app:reindex` still runs it.

        return EXIT_SUCCESS;
    }
}
