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
 * @internal
 */
final class NullReturningCommand extends BaseCommand
{
    protected $group       = 'Fixtures';
    protected $name        = 'null:return';
    protected $description = 'A command that returns null.';

    public function run(array $params)
    {
        return null;
    }
}
