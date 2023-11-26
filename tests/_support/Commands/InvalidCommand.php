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
use CodeIgniter\CLI\CLI;
use CodeIgniter\CodeIgniter;
use ReflectionException;

class InvalidCommand extends BaseCommand
{
    protected $group       = 'demo';
    protected $name        = 'app:invalid';
    protected $description = '';

    public function __construct()
    {
        throw new ReflectionException();
    }

    public function run(array $params): void
    {
        CLI::write('CI Version: ' . CLI::color(CodeIgniter::CI_VERSION, 'red'));
    }
}
