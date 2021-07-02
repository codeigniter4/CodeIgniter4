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
use RuntimeException;

class AppInfo extends BaseCommand
{
    protected $group       = 'demo';
    protected $name        = 'app:info';
    protected $arguments   = ['draft' => 'unused'];
    protected $description = 'Displays basic application information.';

    public function run(array $params)
    {
        CLI::write('CI Version: ' . CLI::color(CodeIgniter::CI_VERSION, 'red'));
    }

    public function bomb()
    {
        try {
            CLI::color('test', 'white', 'Background');
        } catch (RuntimeException $oops) {
            $this->showError($oops);
        }
    }

    public function helpme()
    {
        $this->call('help');
    }
}
