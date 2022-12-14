<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Utilities;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Autoload;

/**
 * Lists namespaces set in Config\Autoload with their
 * full server path. Helps you to verify that you have
 * the namespaces setup correctly.
 */
class Namespaces extends BaseCommand
{
    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'CodeIgniter';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'namespaces';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Verifies your namespaces are setup correctly.';

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = 'namespaces';

    /**
     * the Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * the Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Displays the help for the spark cli script itself.
     */
    public function run(array $params)
    {
        $config = new Autoload();

        $tbody = [];

        foreach ($config->psr4 as $ns => $paths) {
            foreach ((array) $paths as $path) {
                $path = realpath($path) ?: $path;

                $tbody[] = [
                    $ns,
                    $path,
                    is_dir($path) ? 'Yes' : 'MISSING',
                ];
            }
        }

        $thead = [
            'Namespace',
            'Path',
            'Found?',
        ];

        CLI::table($tbody, $thead);
    }
}
