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

use CodeIgniter\CLI\CLI;
use CodeIgniter\Commands\ListCommands as BaseListCommands;

class ListCommands extends BaseListCommands
{
    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'list';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'This is testing to override `list` command.';

    /**
     * the Command's usage
     *
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
