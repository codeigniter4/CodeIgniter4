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

    /**
     * Writes a file to disk, or returns false if not successful.
     *
     * @deprecated 4.6.1 Use `write_file()` instead.
     *
     * @param string $path
     * @param string $data
     * @param string $mode
     */
    protected function writeFile($path, $data, $mode = 'wb'): bool
    {
        if (($fp = @fopen($path, $mode)) === false) {
            return false;
        }

        flock($fp, LOCK_EX);

        $result = 0;

        for ($written = 0, $length = strlen($data); $written < $length; $written += $result) {
            if (($result = fwrite($fp, substr($data, $written))) === false) {
                break;
            }
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        return is_int($result);
    }

    /**
     * Deletes all files contained in the supplied directory path.
     * Files must be writable or owned by the system in order to be deleted.
     * If the second parameter is set to TRUE, any directories contained
     * within the supplied base directory will be nuked as well.
     *
     * @deprecated 4.6.1 Use `delete_files()` instead.
     *
     * @param string $path   File path
     * @param bool   $delDir Whether to delete any directories found in the path
     * @param bool   $htdocs Whether to skip deleting .htaccess and index page files
     * @param int    $_level Current directory depth level (default: 0; internal use only)
     */
    protected function deleteFiles(string $path, bool $delDir = false, bool $htdocs = false, int $_level = 0): bool
    {
        // Trim the trailing slash
        $path = rtrim($path, '/\\');

        if (! $currentDir = @opendir($path)) {
            return false;
        }

        while (false !== ($filename = @readdir($currentDir))) {
            if ($filename !== '.' && $filename !== '..') {
                if (is_dir($path . DIRECTORY_SEPARATOR . $filename) && $filename[0] !== '.') {
                    $this->deleteFiles($path . DIRECTORY_SEPARATOR . $filename, $delDir, $htdocs, $_level + 1);
                } elseif (! $htdocs || preg_match('/^(\.htaccess|index\.(html|htm|php)|web\.config)$/i', $filename) !== 1) {
                    @unlink($path . DIRECTORY_SEPARATOR . $filename);
                }
            }
        }

        closedir($currentDir);

        return ($delDir && $_level > 0) ? @rmdir($path) : true;
    }

    /**
     * Reads the specified directory and builds an array containing the filenames,
     * filesize, dates, and permissions
     *
     * Any sub-folders contained within the specified path are read as well.
     *
     * @deprecated 4.6.1 Use `get_dir_file_info()` instead.
     *
     * @param string $sourceDir    Path to source
     * @param bool   $topLevelOnly Look only at the top level directory specified?
     * @param bool   $_recursion   Internal variable to determine recursion status - do not use in calls
     *
     * @return array<string, array{
     *  name: string,
     *  server_path: string,
     *  size: int,
     *  date: int,
     *  relative_path: string,
     * }>|false
     */
    protected function getDirFileInfo(string $sourceDir, bool $topLevelOnly = true, bool $_recursion = false): array|false
    {
        static $filedata = [];

        $relativePath = $sourceDir;
        $filePointer  = @opendir($sourceDir);

        if (! is_bool($filePointer)) {
            // reset the array and make sure $sourceDir has a trailing slash on the initial call
            if ($_recursion === false) {
                $filedata = [];

                $resolvedSrc = realpath($sourceDir);
                $resolvedSrc = $resolvedSrc === false ? $sourceDir : $resolvedSrc;

                $sourceDir = rtrim($resolvedSrc, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }

            // Used to be foreach (scandir($sourceDir, 1) as $file), but scandir() is simply not as fast
            while (false !== $file = readdir($filePointer)) {
                if (is_dir($sourceDir . $file) && $file[0] !== '.' && $topLevelOnly === false) {
                    $this->getDirFileInfo($sourceDir . $file . DIRECTORY_SEPARATOR, $topLevelOnly, true);
                } elseif (! is_dir($sourceDir . $file) && $file[0] !== '.') {
                    $filedata[$file] = $this->getFileInfo($sourceDir . $file);

                    $filedata[$file]['relative_path'] = $relativePath;
                }
            }

            closedir($filePointer);

            return $filedata;
        }

        return false;
    }

    /**
     * Given a file and path, returns the name, path, size, date modified
     * Second parameter allows you to explicitly declare what information you want returned
     * Options are: name, server_path, size, date, readable, writable, executable, fileperms
     * Returns FALSE if the file cannot be found.
     *
     * @deprecated 4.6.1 Use `get_file_info()` instead.
     *
     * @param string              $file           Path to file
     * @param list<string>|string $returnedValues Array or comma separated string of information returned
     *
     * @return array{
     *  name?: string,
     *  server_path?: string,
     *  size?: int,
     *  date?: int,
     *  readable?: bool,
     *  writable?: bool,
     *  executable?: bool,
     *  fileperms?: int
     * }|false
     */
    protected function getFileInfo(string $file, $returnedValues = ['name', 'server_path', 'size', 'date']): array|false
    {
        if (! is_file($file)) {
            return false;
        }

        if (is_string($returnedValues)) {
            $returnedValues = explode(',', $returnedValues);
        }

        $fileInfo = [];

        foreach ($returnedValues as $key) {
            switch ($key) {
                case 'name':
                    $fileInfo['name'] = basename($file);
                    break;

                case 'server_path':
                    $fileInfo['server_path'] = $file;
                    break;

                case 'size':
                    $fileInfo['size'] = filesize($file);
                    break;

                case 'date':
                    $fileInfo['date'] = filemtime($file);
                    break;

                case 'readable':
                    $fileInfo['readable'] = is_readable($file);
                    break;

                case 'writable':
                    $fileInfo['writable'] = is_writable($file);
                    break;

                case 'executable':
                    $fileInfo['executable'] = is_executable($file);
                    break;

                case 'fileperms':
                    $fileInfo['fileperms'] = fileperms($file);
                    break;
            }
        }

        return $fileInfo;
    }
}
