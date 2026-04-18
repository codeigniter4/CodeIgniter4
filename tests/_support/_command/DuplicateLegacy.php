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

use CodeIgniter\CLI\BaseCommand;

/**
 * @internal
 */
final class DuplicateLegacy extends BaseCommand
{
    protected $group       = 'Fixtures';
    protected $name        = 'dup:test';
    protected $description = 'Legacy fixture that collides with a modern command of the same name.';

    public function run(array $params): int
    {
        return EXIT_SUCCESS;
    }
}
