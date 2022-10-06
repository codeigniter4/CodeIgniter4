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
use Exception;

/**
 * Console
 */
class Console
{
    /**
     * Main CodeIgniter instance.
     *
     * @var CodeIgniter
     */
    protected $app;

    public function __construct(CodeIgniter $app)
    {
        $this->app = $app;
    }

    /**
     * Runs the current command discovered on the CLI.
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function run(bool $useSafeOutput = false)
    {
        $path = CLI::getURI() ?: 'list';

        // Set the path for the application to route to.
        $this->app->setPath("ci{$path}");

        return $this->app->useSafeOutput($useSafeOutput)->run();
    }

    /**
     * Displays basic information about the Console.
     */
    public function showHeader(bool $suppress = false)
    {
        if ($suppress) {
            return;
        }

        CLI::write(sprintf('CodeIgniter v%s Command Line Tool - Server Time: %s UTC%s', CodeIgniter::CI_VERSION, date('Y-m-d H:i:s'), date('P')), 'green');
        CLI::newLine();
    }
}
