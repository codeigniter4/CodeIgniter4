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

namespace Tests\Support\Commands\Legacy;

use CodeIgniter\CLI\BaseCommand;

/**
 * Test fixture only. Exercises that a legacy `BaseCommand` can invoke a modern
 * `AbstractCommand` via {@see \CodeIgniter\CLI\Commands::runCommand()}. Not a
 * pattern to follow in application code — migrate legacy commands to
 * `AbstractCommand` instead.
 */
final class HelpLegacyCommand extends BaseCommand
{
    protected $group       = 'Fixtures';
    protected $name        = 'help:legacy';
    protected $description = 'Legacy command to call the help command.';

    public function run(array $params): int
    {
        return $this->commands->runCommand('help', [], []);
    }
}
