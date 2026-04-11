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

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CodeIgniter;
use CodeIgniter\Exceptions\RuntimeException;

/**
 * @internal
 */
final class AppInfo extends BaseCommand
{
    protected $group       = 'demo';
    protected $name        = 'app:info';
    protected $arguments   = ['draft' => 'unused'];
    protected $description = 'Displays basic application information.';

    public function run(array $params): int
    {
        CLI::write(sprintf('CodeIgniter Version: %s', CodeIgniter::CI_VERSION));

        return 0;
    }

    public function bomb(): int
    {
        try {
            CLI::color('test', 'white', 'Background');
        } catch (RuntimeException $e) {
            $this->showError($e);

            return 1;
        }

        return 0;
    }

    public function helpMe(): int
    {
        $this->call('help');

        return 0;
    }
}
