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
use CodeIgniter\Publisher\Publisher;

/**
 * Discovers all Publisher classes from the "Publishers/" directory
 * across namespaces. Executes `publish()` from each instance, parsing
 * each result.
 */
class Publish extends BaseCommand
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
    protected $name = 'publish';

    /**
     * The Command's short description
     *
     * @var string
     */
    protected $description = 'Discovers and executes all predefined Publisher classes.';

    /**
     * The Command's usage
     *
     * @var string
     */
    protected $usage = 'publish [<directory>]';

    /**
     * The Command's arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'directory' => '[Optional] The directory to scan within each namespace. Default: "Publishers".',
    ];

    /**
     * the Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [];

    /**
     * Displays the help for the spark cli script itself.
     */
    public function run(array $params)
    {
        $directory = array_shift($params) ?? 'Publishers';

        if ([] === $publishers = Publisher::discover($directory)) {
            CLI::write(lang('Publisher.publishMissing', [$directory]));

            return;
        }

        foreach ($publishers as $publisher) {
            if ($publisher->publish()) {
                CLI::write(lang('Publisher.publishSuccess', [
                    get_class($publisher),
                    count($publisher->getPublished()),
                    $publisher->getDestination(),
                ]), 'green');
            } else {
                CLI::error(lang('Publisher.publishFailure', [
                    get_class($publisher),
                    $publisher->getDestination(),
                ]), 'light_gray', 'red');

                foreach ($publisher->getErrors() as $file => $exception) {
                    CLI::write($file);
                    CLI::error($exception->getMessage());
                    CLI::newLine();
                }
            }
        }
    }
}
