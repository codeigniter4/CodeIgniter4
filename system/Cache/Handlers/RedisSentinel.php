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

namespace CodeIgniter\Cache\Handlers;

use Redis;
use RedisException;
use RuntimeException;

/**
 * Discovers the current Redis master from a list of Sentinel nodes.
 *
 * phpredis has no built-in Sentinel failover handling, so the Redis cache
 * and session handlers use this utility to resolve the master address before
 * connecting. It prefers the `RedisSentinel` class (phpredis >= 5.3) and
 * falls back to a plain `SENTINEL get-master-addr-by-name` command for older
 * versions.
 */
class RedisSentinel
{
    /**
     * Default Sentinel port.
     */
    private const DEFAULT_SENTINEL_PORT = 26379;

    /**
     * Queries the given Sentinel nodes for the address of the named master.
     *
     * Each node is tried in order; the first one that answers wins. When no
     * node can return the master address a RuntimeException is thrown so the
     * caller can surface a clear error.
     *
     * @param list<array{host: string, port?: int}> $nodes   Sentinel nodes to query.
     * @param string                                $service Sentinel master name, e.g. "mymaster".
     * @param float                                 $timeout Connection timeout (seconds) per node.
     *
     * @return array{0: string, 1: int} The master host and port.
     *
     * @throws RuntimeException When no Sentinel node can discover the master.
     */
    public static function discoverMaster(array $nodes, string $service, float $timeout = 0.0): array
    {
        if ($nodes === []) {
            throw new RuntimeException('No Redis Sentinel nodes configured.');
        }

        foreach ($nodes as $node) {
            $host = $node['host'] ?? '';
            $port = $node['port'] ?? self::DEFAULT_SENTINEL_PORT;

            if ($host === '') {
                continue;
            }

            $address = self::queryNode($host, (int) $port, $service, $timeout);

            if ($address !== null) {
                return $address;
            }
        }

        throw new RuntimeException(sprintf('Redis Sentinel unable to discover master "%s".', $service));
    }

    /**
     * Queries a single Sentinel node for the master address.
     *
     * @return array{0: string, 1: int}|null
     */
    private static function queryNode(string $host, int $port, string $service, float $timeout): ?array
    {
        // Prefer the dedicated RedisSentinel class (phpredis >= 5.3).
        if (class_exists(\RedisSentinel::class)) {
            try {
                $sentinel = new \RedisSentinel($host, $port, $timeout);
                $result   = $sentinel->getMasterAddrByName($service);

                if ($result === false) {
                    return null;
                }

                return self::normalise($result);
            } catch (RedisException) {
                // Node unreachable or command failed; try the next one.
                return null;
            }
        }

        // Fall back to the SENTINEL command on a plain Redis connection.
        try {
            $redis = new Redis();
            $redis->connect($host, $port, $timeout);

            $result = $redis->rawcommand('SENTINEL', 'get-master-addr-by-name', $service);

            try {
                $redis->close();
            } catch (RedisException) {
                // Connection already dead, that's fine.
            }

            if ($result === false) {
                return null;
            }

            return self::normalise($result);
        } catch (RedisException) {
            return null;
        }
    }

    /**
     * Normalises the varied reply shapes into a [host, port] pair.
     *
     * phpredis returns either a flat `['host', 'port']` list (rawCommand and
     * most RedisSentinel builds) or an associative `[['ip' => .., 'port' => ..]]`
     * shape on some builds. Both are coerced to `array{0:string, 1:int}`.
     *
     * @param array<array-key, mixed> $result
     *
     * @return array{0: string, 1: int}|null
     */
    private static function normalise(array $result): ?array
    {
        // Some RedisSentinel builds wrap the entry in an outer array.
        $entry = array_is_list($result) && isset($result[0]) && is_array($result[0])
            ? $result[0]
            : $result;

        if (isset($entry['ip'], $entry['port'])) {
            return [(string) $entry['ip'], (int) $entry['port']];
        }

        if (isset($entry[0], $entry[1]) && is_string($entry[0]) && (is_string($entry[1]) || is_int($entry[1]))) {
            return [(string) $entry[0], (int) $entry[1]];
        }

        return null;
    }
}
