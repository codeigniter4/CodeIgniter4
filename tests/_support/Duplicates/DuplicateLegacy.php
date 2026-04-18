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

use CodeIgniter\CLI\BaseCommand;

/**
 * Lives outside any `Commands/` directory so discovery does not pick it up
 * automatically. Tests inject this via a mocked FileLocator.
 *
 * @internal
 */
final class DuplicateLegacy extends BaseCommand
{
    protected $group       = 'Duplicates';
    protected $name        = 'dup:test';
    protected $description = 'Legacy fixture that collides with a modern command of the same name.';

    public function run(array $params): int
    {
        return EXIT_SUCCESS;
    }
}
