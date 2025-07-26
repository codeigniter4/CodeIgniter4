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

namespace CodeIgniter\CLI;

use CodeIgniter\CodeIgniter;
use Config\App;
use Config\Services;
use Exception;

/**
 * Console
 *
 * @see \CodeIgniter\CLI\ConsoleTest
 */
class Console
{
    /**
     * Runs the current command discovered on the CLI.
     *
     * @return int|void Exit code
     *
     * @throws Exception
     */
    public function run()
    {
        // Create CLIRequest
        $appConfig = config(App::class);
        Services::createRequest($appConfig, true);
        // Load Routes
        service('routes')->loadRoutes();

        $params  = array_merge(CLI::getSegments(), CLI::getOptions());
        $params  = $this->parseParamsForHelpOption($params);
        $command = array_shift($params) ?? 'list';

        return service('commands')->run($command, $params);
    }

    /**
     * Displays basic information about the Console.
     *
     * @return void
     */
    public function showHeader(bool $suppress = false)
    {
        if ($suppress) {
            return;
        }

        CLI::write(sprintf(
            'CodeIgniter v%s Command Line Tool - Server Time: %s',
            CodeIgniter::CI_VERSION,
            date('Y-m-d H:i:s \\U\\T\\CP'),
        ), 'green');
        CLI::newLine();
    }

    /**
     * Introspects the `$params` passed for presence of the
     * `--help` option.
     *
     * If present, it will be found as `['help' => null]`.
     * We'll remove that as an option from `$params` and
     * unshift it as argument instead.
     *
     * @param array<int|string, string|null> $params
     */
    private function parseParamsForHelpOption(array $params): array
    {
        if (array_key_exists('help', $params)) {
            unset($params['help']);

            $params = $params === [] ? ['list'] : $params;
            array_unshift($params, 'help');
        }

        return $params;
    }
}
