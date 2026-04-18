<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Argument;

#[Command(name: 'app:about', description: 'This is testing to override `app:about` command.', group: 'App')]
final class AppAboutCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->addArgument(new Argument(name: 'unused', description: 'This argument is not used.', required: true));
    }

    protected function execute(array $arguments, array $options): int
    {
        CLI::write('This is ' . self::class);

        return EXIT_SUCCESS;
    }
}
