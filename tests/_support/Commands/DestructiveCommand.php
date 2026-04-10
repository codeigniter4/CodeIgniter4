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

namespace Tests\Support\Commands;

use RuntimeException;

/**
 * @internal
 */
final class DestructiveCommand extends AbstractInfo
{
    protected $group       = 'demo';
    protected $name        = 'app:destructive';
    protected $description = 'This command is destructive.';

    public function run(array $params): never
    {
        throw new RuntimeException('This command is destructive and should not be run.');
    }
}
