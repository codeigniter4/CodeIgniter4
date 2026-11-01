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

use CodeIgniter\Cache\Exceptions\CacheException;
use CodeIgniter\Cache\LockStoreInterface;
use CodeIgniter\Cache\LockStoreProviderInterface;
use CodeIgniter\Cache\LockStores\FileLockStore;
use CodeIgniter\I18n\Time;
use Config\Cache;
use FilesystemIterator;
use Throwable;
use UnexpectedValueException;

/**
 * File system cache handler
 */
class FileHandler extends BaseHandler implements LockStoreProviderInterface
{
    /**
     * Maximum key length.
     */
    public const MAX_KEY_LENGTH = 255;

    /**
     * Where to store cached files on the disk.
     *
     * @var string
     */
    protected $path;

    /**
     * Mode for the stored files.
     * Must be chmod-safe (octal).
     *
     * @var int
     *
     * @see https://www.php.net/manual/en/function.chmod.php
     */
    protected $mode;

    private readonly LockStoreInterface $lockStore;

    /**
     * Note: Use `CacheFactory::getHandler()` to instantiate.
     *
     * @throws CacheException
     */
    public function __construct(Cache $config)
    {
        $options = [
            ...['storePath' => WRITEPATH . 'cache', 'mode' => 0640],
            ...$config->file,
        ];

        $this->path = $options['storePath'] !== '' ? $options['storePath'] : WRITEPATH . 'cache';
        $this->path = rtrim($this->path, '\\/') . '/';

        if (! is_really_writable($this->path)) {
            throw CacheException::forUnableToWrite($this->path);
        }

        $this->mode   = $options['mode'];
        $this->prefix = $config->prefix;

        $this->lockStore = new FileLockStore($this->path, $this->mode, $this->prefix);

        helper('filesystem');
    }

    public function initialize(): void
    {
    }

    public function get(string $key): mixed
    {
        $key  = static::validateKey($key, $this->prefix);
        $data = $this->getItem($key);

        return is_array($data) ? $data['data'] : null;
    }

    public function save(string $key, mixed $value, int $ttl = 60): bool
    {
        $key = static::validateKey($key, $this->prefix);

        $contents = [
            'time' => Time::now()->getTimestamp(),
            'ttl'  => $ttl,
            'data' => $value,
        ];

        if (write_file($this->path . $key, serialize($contents))) {
            try {
                chmod($this->path . $key, $this->mode);

                // @codeCoverageIgnoreStart
            } catch (Throwable $e) {
                log_message('debug', 'Failed to set mode on cache file: ' . $e);
                // @codeCoverageIgnoreEnd
            }

            return true;
        }

        return false;
    }

    public function delete(string $key): bool
    {
        $key = static::validateKey($key, $this->prefix);

        return is_file($this->path . $key) && unlink($this->path . $key);
    }

    public function deleteMatching(string $pattern): int
    {
        // The pattern is matched against file names only and never becomes part
        // of a path, so it cannot reach files outside the cache directory.
        // Passing the cache path to glob() would also interpret special
        // characters in the path (e.g. "[") as part of the pattern.
        try {
            $iterator = new FilesystemIterator($this->path, FilesystemIterator::SKIP_DOTS);
        } catch (UnexpectedValueException) {
            return 0;
        }

        $deleted = 0;

        foreach ($iterator as $file) {
            // FNM_PERIOD keeps glob() semantics: wildcards don't match a leading dot.
            if (! fnmatch($pattern, $file->getFilename(), FNM_PERIOD)) {
                continue;
            }

            if ($file->isFile() && @unlink($file->getPathname())) {
                $deleted++;
            }
        }

        return $deleted;
    }

    public function increment(string $key, int $offset = 1): bool|int
    {
        $prefixedKey = static::validateKey($key, $this->prefix);
        $tmp         = $this->getItem($prefixedKey);

        if ($tmp === false) {
            $tmp = ['data' => 0, 'ttl' => 60];
        }

        ['data' => $value, 'ttl' => $ttl] = $tmp;

        if (! is_int($value)) {
            return false;
        }

        $value += $offset;

        return $this->save($key, $value, $ttl) ? $value : false;
    }

    public function decrement(string $key, int $offset = 1): bool|int
    {
        return $this->increment($key, -$offset);
    }

    public function clean(): bool
    {
        return delete_files($this->path, false, true);
    }

    public function getCacheInfo(): array
    {
        return get_dir_file_info($this->path);
    }

    public function getMetaData(string $key): ?array
    {
        $key = static::validateKey($key, $this->prefix);

        if (false === $data = $this->getItem($key)) {
            return null;
        }

        return [
            'expire' => $data['ttl'] > 0 ? $data['time'] + $data['ttl'] : null,
            'mtime'  => filemtime($this->path . $key),
            'data'   => $data['data'],
        ];
    }

    public function isSupported(): bool
    {
        return is_writable($this->path);
    }

    public function lockStore(): LockStoreInterface
    {
        return $this->lockStore;
    }

    /**
     * Does the heavy lifting of actually retrieving the file and
     * verifying its age.
     *
     * @return array{data: mixed, ttl: int, time: int}|false
     */
    protected function getItem(string $filename): array|false
    {
        if (! is_file($this->path . $filename)) {
            return false;
        }

        $content = @file_get_contents($this->path . $filename);

        if ($content === false) {
            return false;
        }

        try {
            $data = unserialize($content);
        } catch (Throwable) {
            return false;
        }

        if (! is_array($data)) {
            return false;
        }

        if (! isset($data['ttl']) || ! is_int($data['ttl'])) {
            return false;
        }

        if (! isset($data['time']) || ! is_int($data['time'])) {
            return false;
        }

        if ($data['ttl'] > 0 && Time::now()->getTimestamp() > $data['time'] + $data['ttl']) {
            @unlink($this->path . $filename);

            return false;
        }

        return $data;
    }
}
