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

#[Command(name: 'test:calls-about', description: 'Fixture that calls app:about.', group: 'Fixtures')]
final class CallsAboutCommand extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        return $this->call('app:about', ['a']);
    }
}
