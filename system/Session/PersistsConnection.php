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

namespace CodeIgniter\Session;

/**
 * Trait for session handlers that need persistent connections.
 */
trait PersistsConnection
{
    /**
     * Connection pool keyed by connection identifier.
     * Allows multiple configurations to each have their own connection.
     *
     * @var array<string, object>
     */
    protected static $connectionPool = [];

    /**
     * Get connection identifier based on configuration.
     * This returns a unique hash for each distinct connection configuration.
     */
    protected function getConnectionIdentifier(): string
    {
        return hash('xxh128', serialize([
            'class'     => static::class,
            'savePath'  => $this->savePath,
            'keyPrefix' => $this->keyPrefix,
        ]));
    }

    /**
     * Check if a persistent connection exists for this configuration.
     */
    protected function hasPersistentConnection(): bool
    {
        $identifier = $this->getConnectionIdentifier();

        return isset(self::$connectionPool[$identifier]);
    }

    /**
     * Get the persistent connection for this configuration.
     */
    protected function getPersistentConnection(): ?object
    {
        $identifier = $this->getConnectionIdentifier();

        return self::$connectionPool[$identifier] ?? null;
    }

    /**
     * Store a connection for persistence.
     *
     * @param object|null $connection The connection to persist (null to clear).
     */
    protected function setPersistentConnection(?object $connection): void
    {
        $identifier = $this->getConnectionIdentifier();

        if ($connection === null) {
            unset(self::$connectionPool[$identifier]);
        } else {
            self::$connectionPool[$identifier] = $connection;
        }
    }

    /**
     * Reset all persistent connections (useful for testing).
     */
    public static function resetPersistentConnections(): void
    {
        self::$connectionPool = [];
    }
}
