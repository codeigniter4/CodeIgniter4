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

namespace Tests\Support\Duplicates;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;

/**
 * Lives outside any `Commands/` directory so discovery does not pick it up
 * automatically. Tests inject this via a mocked FileLocator.
 *
 * @internal
 */
#[Command(name: 'dup:test', description: 'Modern fixture that collides with a legacy command of the same name.', group: 'Duplicates')]
final class DuplicateModern extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        return EXIT_SUCCESS;
    }
}
