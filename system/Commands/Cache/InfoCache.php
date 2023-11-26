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
use CodeIgniter\I18n\Time;
use Config\Cache;

/**
 * Shows information on the cache.
 */
class InfoCache extends BaseCommand
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
    protected $name = 'cache:info';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Shows file cache information in the current system.';

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = 'cache:info';

    /**
     * Clears the cache
     */
    public function run(array $params)
    {
        $config = config(Cache::class);
        helper('number');

        if ($config->handler !== 'file') {
            CLI::error('This command only supports the file cache handler.');

            return;
        }

        $cache  = CacheFactory::getHandler($config);
        $caches = $cache->getCacheInfo();
        $tbody  = [];

        foreach ($caches as $key => $field) {
            $tbody[] = [
                $key,
                clean_path($field['server_path']),
                number_to_size($field['size']),
                Time::createFromTimestamp($field['date']),
            ];
        }

        $thead = [
            CLI::color('Name', 'green'),
            CLI::color('Server Path', 'green'),
            CLI::color('Size', 'green'),
            CLI::color('Date', 'green'),
        ];

        CLI::table($tbody, $thead);
    }
}
