<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Commands;

use CodeIgniter\CLI\BaseCommand;

abstract class AbstractInfo extends BaseCommand
{
    protected $group       = 'demo';
    protected $name        = 'app:pablo';
    protected $description = 'Displays basic application information.';
}
