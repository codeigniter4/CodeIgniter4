<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\CLI;

use CodeIgniter\CodeIgniter;
use Config\Services;
use Exception;

/**
 * Console
 */
class Console
{
    /**
     * Runs the current command discovered on the CLI.
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function run()
    {
        $runner  = Services::commands();
        $params  = array_merge(CLI::getSegments(), CLI::getOptions());
        $command = array_shift($params) ?? 'list';

        return $runner->run($command, $params);
    }

    /**
     * Displays basic information about the Console.
     */
    public function showHeader(bool $suppress = false)
    {
        if ($suppress) {
            return;
        }

        CLI::write(sprintf(
            'CodeIgniter v%s Command Line Tool - Server Time: %s UTC%s',
            CodeIgniter::CI_VERSION,
            date('Y-m-d H:i:s'),
            date('P')
        ), 'green');
        CLI::newLine();
    }
}
