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
final class ListCommands extends BaseCommand
{
    /**
     * @var string
     */
    protected $group = 'App';

    /**
     * @var string
     */
    protected $name = 'list';

    /**
     * @var string
     */
    protected $description = 'This is testing to override `list` command.';

    /**
     * @var string
     */
    protected $usage = 'list';

    /**
     * Displays the help for the spark cli script itself.
     */
    public function run(array $params)
    {
        CLI::write('This is ' . self::class);

        return EXIT_SUCCESS;
    }
}
