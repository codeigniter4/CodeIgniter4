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

namespace Tests\Support\Commands\Modern;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\CLI;

#[Command(
    name: 'fixture:aliased',
    description: 'Fixture command exercising command aliases.',
    group: 'Fixtures',
    aliases: ['fixture:alias', 'fa'],
)]
final class AliasedCommand extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        CLI::write('Ran fixture:aliased.');

        return EXIT_SUCCESS;
    }
}
