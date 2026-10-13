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
use CodeIgniter\CLI\Output;

#[Command(name: 'test:output-probe', description: 'Fixture that writes through the output and the facade.', group: 'Fixtures')]
final class OutputProbeCommand extends AbstractCommand
{
    public ?Output $seen = null;

    protected function execute(array $arguments, array $options): int
    {
        $this->seen = $this->getOutput();

        $this->getOutput()->write('from output');
        CLI::write('from facade');

        return EXIT_SUCCESS;
    }
}
