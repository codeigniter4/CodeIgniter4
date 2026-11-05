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

#[Command(
    name: 'orchestrator:probe',
    description: 'Reports its stdin and environment, then fails.',
    group: 'Tests',
)]
final class OrchestratorProbe extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        $stdin = stream_get_contents(STDIN);

        fwrite(STDOUT, sprintf('stdin=%s env=%s', $stdin, (string) getenv('ORCHESTRATOR_PROBE')));
        fwrite(STDERR, 'probe-error');

        if (getenv('ORCHESTRATOR_STUBBORN') === '1') {
            pcntl_signal(SIGTERM, SIG_IGN);
        }

        if (getenv('ORCHESTRATOR_SLEEP') === '1' || getenv('ORCHESTRATOR_STUBBORN') === '1') {
            sleep(30);
        }

        if (getenv('ORCHESTRATOR_FLOOD') === '1') {
            fwrite(STDOUT, str_repeat('o', 300_000));
            fwrite(STDERR, str_repeat('e', 300_000));
        }

        return 3;
    }
}
