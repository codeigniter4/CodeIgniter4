<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Cache;

use CodeIgniter\Cache\Exceptions\CacheException;
use CodeIgniter\Exceptions\CriticalError;
use CodeIgniter\Test\Mock\MockCache;
use Config\Cache;

/**
 * A factory for loading the desired
 *
 * @see \CodeIgniter\Cache\CacheFactoryTest
 */
class CacheFactory
{
    /**
     * The class to use when mocking
     *
     * @var string
     */
    public static $mockClass = MockCache::class;

    /**
     * The service to inject the mock as
     *
     * @var string
     */
    public static $mockServiceName = 'cache';

    /**
     * Attempts to create the desired cache handler, based upon the
     *
     * @return CacheInterface
     */
    public static function getHandler(Cache $config, ?string $handler = null, ?string $backup = null)
    {
        if (! isset($config->validHandlers) || $config->validHandlers === []) {
            throw CacheException::forInvalidHandlers();
        }

        if (! isset($config->handler) || ! isset($config->backupHandler)) {
            throw CacheException::forNoBackup();
        }

        $handler = ! empty($handler) ? $handler : $config->handler;
        $backup  = ! empty($backup) ? $backup : $config->backupHandler;

        if (! array_key_exists($handler, $config->validHandlers) || ! array_key_exists($backup, $config->validHandlers)) {
            throw CacheException::forHandlerNotFound();
        }

        $adapter = new $config->validHandlers[$handler]($config);

        if (! $adapter->isSupported()) {
            $adapter = new $config->validHandlers[$backup]($config);

            if (! $adapter->isSupported()) {
                // Fall back to the dummy adapter.
                $adapter = new $config->validHandlers['dummy']();
            }
        }

        // If $adapter->initialization throws a CriticalError exception, we will attempt to
        // use the $backup handler, if that also fails, we resort to the dummy handler.
        try {
            $adapter->initialize();
        } catch (CriticalError $e) {
            log_message('critical', $e . ' Resorting to using ' . $backup . ' handler.');

            // get the next best cache handler (or dummy if the $backup also fails)
            $adapter = self::getHandler($config, $backup, 'dummy');
        }

        return $adapter;
    }
}
