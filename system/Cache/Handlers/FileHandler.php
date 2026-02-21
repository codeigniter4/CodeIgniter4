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
use CodeIgniter\I18n\Time;
use Config\Cache;
use Throwable;

/**
 * File system cache handler
 *
 * @see \CodeIgniter\Cache\Handlers\FileHandlerTest
 */
class FileHandler extends BaseHandler
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
        $deleted = 0;

        foreach (glob($this->path . $pattern, GLOB_NOSORT) as $filename) {
            if (is_file($filename) && @unlink($filename)) {
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
