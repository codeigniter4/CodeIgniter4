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
use CodeIgniter\CLI\CLI;

/**
 * @internal
 */
final class AppInfo extends BaseCommand
{
    /**
     * @var string
     */
    protected $group = 'App';

    /**
     * @var string
     */
    protected $name = 'app:info';

    /**
     * @var string
     */
    protected $description = 'This is testing to override `app:info` command.';

    /**
     * @var string
     */
    protected $usage = 'app:info';

    /**
     * Displays the help for the spark cli script itself.
     */
    public function run(array $params)
    {
        CLI::write('This is ' . self::class);

        return EXIT_SUCCESS;
    }
}
