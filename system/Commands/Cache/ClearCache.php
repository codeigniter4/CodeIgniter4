<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Cache;

use CodeIgniter\Cache\CacheFactory;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Cache;

/**
 * Clears current cache.
 */
class ClearCache extends BaseCommand
{
    /**
     * Command grouping.
     *
     * @var string
     */
    protected $group = 'Cache';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'cache:clear';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Clears the current system caches.';

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = 'cache:clear [<driver>]';

    /**
     * the Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'driver' => 'The cache driver to use',
    ];

    /**
     * Clears the cache
     */
    public function run(array $params)
    {
        $config  = config(Cache::class);
        $handler = $params[0] ?? $config->handler;

        if (! array_key_exists($handler, $config->validHandlers)) {
            CLI::error($handler . ' is not a valid cache handler.');

            return;
        }

        $config->handler = $handler;
        $cache           = CacheFactory::getHandler($config);

        if (! $cache->clean()) {
            // @codeCoverageIgnoreStart
            CLI::error('Error while clearing the cache.');

            return;
            // @codeCoverageIgnoreEnd
        }

        CLI::write(CLI::color('Cache cleared.', 'green'));
    }
}
