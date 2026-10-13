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
use CodeIgniter\CLI\Output;

#[Command(name: 'test:silent-call', description: 'Fixture that calls help silently, then writes.', group: 'Fixtures')]
final class SilentCallCommand extends AbstractCommand
{
    public ?Output $afterSilentCall = null;

    protected function execute(array $arguments, array $options): int
    {
        $this->callSilently('help');

        $this->afterSilentCall = $this->getOutput();
        $this->getOutput()->write('after');

        return EXIT_SUCCESS;
    }
}
