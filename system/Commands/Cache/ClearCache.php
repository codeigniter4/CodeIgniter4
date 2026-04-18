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

namespace CodeIgniter\Commands\Cache;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Argument;
use Config\Cache;

/**
 * Clears the current system caches.
 */
#[Command(name: 'cache:clear', description: 'Clears the current system caches.', group: 'Cache')]
class ClearCache extends AbstractCommand
{
    protected function configure(): void
    {
        $this->addArgument(new Argument(
            name: 'driver',
            description: 'The cache driver to use.',
            default: config(Cache::class)->handler,
        ));
    }

    protected function execute(array $arguments, array $options): int
    {
        $driver = $arguments['driver'];
        $config = config(Cache::class);

        if (! array_key_exists($driver, $config->validHandlers)) {
            CLI::error(lang('Cache.invalidHandler', [$driver]));

            return EXIT_ERROR;
        }

        $config->handler = $driver;

        if (! service('cache', $config)->clean()) {
            CLI::error('Error occurred while clearing the cache.');

            return EXIT_ERROR;
        }

        CLI::write('Cache cleared.', 'green');

        return EXIT_SUCCESS;
    }
}
