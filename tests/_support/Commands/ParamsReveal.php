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

class ParamsReveal extends BaseCommand
{
    protected $group       = 'demo';
    protected $name        = 'reveal';
    protected $usage       = 'reveal [options] [arguments]';
    protected $description = 'Reveal params';
    public static $args;

    public function run(array $params): void
    {
        static::$args = $params;
    }
}
