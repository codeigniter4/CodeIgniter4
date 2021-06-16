<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\CLI;

use CodeIgniter\CodeIgniter;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
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

    //--------------------------------------------------------------------

    /**
     * Console constructor.
     *
     * @param CodeIgniter $app
     */
    public function __construct(CodeIgniter $app)
    {
        $this->app = $app;
    }

    //--------------------------------------------------------------------

    /**
     * Runs the current command discovered on the CLI.
     *
     * @param bool $useSafeOutput
     *
     * @throws Exception
     *
     * @return mixed|RequestInterface|Response|ResponseInterface
     */
    public function run(bool $useSafeOutput = false)
    {
        $path = CLI::getURI() ?: 'list';

        // Set the path for the application to route to.
        $this->app->setPath("ci{$path}");

        return $this->app->useSafeOutput($useSafeOutput)->run();
    }

    //--------------------------------------------------------------------

    /**
     * Displays basic information about the Console.
     *
     * @param bool $suppress
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
