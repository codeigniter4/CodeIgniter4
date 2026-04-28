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

namespace CodeIgniter\Cache\LockStores;

use CodeIgniter\Cache\Handlers\FileHandler;
use CodeIgniter\Cache\LockStoreInterface;
use CodeIgniter\I18n\Time;
use Throwable;

class FileLockStore implements LockStoreInterface
{
    public function __construct(
        private readonly string $path,
        private readonly int $mode,
        private readonly string $prefix = '',
    ) {
    }

    public function acquireLock(string $key, string $owner, int $ttl): bool
    {
        return $this->withLockFile($key, static function ($handle) use ($owner, $ttl): bool {
            $data = self::readLockData($handle);
            $now  = Time::now()->getTimestamp();

            if ($data !== null && $data['expires'] > $now) {
                return false;
            }

            return self::writeLockData($handle, $owner, $now + $ttl);
        });
    }

    public function releaseLock(string $key, string $owner): bool
    {
        return $this->withLockFile($key, static function ($handle) use ($owner): bool {
            $data = self::readLockData($handle);

            if ($data === null || $data['owner'] !== $owner) {
                return false;
            }

            return self::clearLockFile($handle);
        });
    }

    public function forceReleaseLock(string $key): bool
    {
        return $this->withLockFile($key, static fn ($handle): bool => self::clearLockFile($handle));
    }

    public function refreshLock(string $key, string $owner, int $ttl): bool
    {
        return $this->withLockFile($key, static function ($handle) use ($owner, $ttl): bool {
            $data = self::readLockData($handle);
            $now  = Time::now()->getTimestamp();

            if ($data === null || $data['owner'] !== $owner || $data['expires'] <= $now) {
                return false;
            }

            return self::writeLockData($handle, $owner, $now + $ttl);
        });
    }

    public function getLockOwner(string $key): ?string
    {
        $owner = null;

        $this->withLockFile($key, static function ($handle) use (&$owner): bool {
            $data = self::readLockData($handle);

            if ($data === null) {
                return true;
            }

            if ($data['expires'] <= Time::now()->getTimestamp()) {
                self::clearLockFile($handle);

                return true;
            }

            $owner = $data['owner'];

            return true;
        }, false);

        return $owner;
    }

    /**
     * @param callable(resource): bool $callback
     */
    private function withLockFile(string $key, callable $callback, bool $create = true): bool
    {
        $key    = FileHandler::validateKey($key, $this->prefix);
        $handle = @fopen($this->path . $key, $create ? 'c+b' : 'r+b');

        if ($handle === false) {
            return false;
        }

        try {
            if (! flock($handle, LOCK_EX)) {
                return false;
            }

            return $callback($handle);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);

            if (is_file($this->path . $key)) {
                try {
                    chmod($this->path . $key, $this->mode);
                } catch (Throwable $e) {
                    log_message('debug', 'Failed to set mode on cache lock file: ' . $e);
                }
            }
        }
    }

    /**
     * @param resource $handle
     *
     * @return array{owner: string, expires: int}|null
     */
    private static function readLockData($handle): ?array
    {
        rewind($handle);

        $content = stream_get_contents($handle);

        if ($content === false || $content === '') {
            return null;
        }

        try {
            $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        if (! is_array($data) || ! isset($data['owner'], $data['expires']) || ! is_string($data['owner']) || ! is_int($data['expires'])) {
            return null;
        }

        return $data;
    }

    /**
     * @param resource $handle
     */
    private static function writeLockData($handle, string $owner, int $expires): bool
    {
        rewind($handle);

        if (! ftruncate($handle, 0)) {
            return false;
        }

        try {
            $content = json_encode(['owner' => $owner, 'expires' => $expires], JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return false;
        }

        if (fwrite($handle, $content) === false) {
            return false;
        }

        return fflush($handle);
    }

    /**
     * @param resource $handle
     */
    private static function clearLockFile($handle): bool
    {
        rewind($handle);

        return ftruncate($handle, 0) && fflush($handle);
    }
}
