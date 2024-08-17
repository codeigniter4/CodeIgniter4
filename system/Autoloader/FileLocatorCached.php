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

namespace CodeIgniter\Autoloader;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Cache\FactoriesCache\FileVarExportHandler;

/**
 * FileLocator with Cache
 *
 * @see \CodeIgniter\Autoloader\FileLocatorCachedTest
 */
final class FileLocatorCached implements FileLocatorInterface
{
    private readonly CacheInterface|FileVarExportHandler $cacheHandler;

    /**
     * Cache data
     *
     * [method => data]
     * E.g.,
     * [
     *     'search' => [$path => $foundPaths],
     * ]
     */
    private array $cache = [];

    /**
     * Is the cache updated?
     */
    private bool $cacheUpdated = false;

    private string $cacheKey = 'FileLocatorCache';

    public function __construct(
        private readonly FileLocator $locator,
        CacheInterface|FileVarExportHandler|null $cache = null
    ) {
        $this->cacheHandler = $cache ?? new FileVarExportHandler();
        $this->loadCache();
    }

    private function loadCache(): void
    {
        $data = $this->cacheHandler->get($this->cacheKey);

        if (is_array($data)) {
            $this->cache = $data;
        }
    }

    public function __destruct()
    {
        $this->saveCache();
    }

    private function saveCache(): void
    {
        if ($this->cacheUpdated) {
            $this->cacheHandler->save($this->cacheKey, $this->cache, 3600 * 24);
        }
    }

    /**
     * Delete cache data
     */
    public function deleteCache(): void
    {
        $this->cacheUpdated = false;
        $this->cacheHandler->delete($this->cacheKey);
    }

    /**
     * Find the qualified name of a file according to
     * the namespace of the first matched namespace path.
     *
     * @return false|string The qualified name or false if the path is not found
     */
    public function findQualifiedNameFromPath(string $path): false|string
    {
        if (isset($this->cache['findQualifiedNameFromPath'][$path])) {
            return $this->cache['findQualifiedNameFromPath'][$path];
        }

        $classname = $this->locator->findQualifiedNameFromPath($path);

        $this->cache['findQualifiedNameFromPath'][$path] = $classname;
        $this->cacheUpdated                              = true;

        return $classname;
    }

    /**
     * Examines a file and returns the fully qualified class name.
     */
    public function getClassname(string $file): string
    {
        if (isset($this->cache['getClassname'][$file])) {
            return $this->cache['getClassname'][$file];
        }

        $classname = $this->locator->getClassname($file);

        $this->cache['getClassname'][$file] = $classname;
        $this->cacheUpdated                 = true;

        return $classname;
    }

    /**
     * Searches through all of the defined namespaces looking for a file.
     * Returns an array of all found locations for the defined file.
     *
     * Example:
     *
     *  $locator->search('Config/Routes.php');
     *  // Assuming PSR4 namespaces include foo and bar, might return:
     *  [
     *      'app/Modules/foo/Config/Routes.php',
     *      'app/Modules/bar/Config/Routes.php',
     *  ]
     *
     * @return list<string> List of file paths
     */
    public function search(string $path, string $ext = 'php', bool $prioritizeApp = true): array
    {
        if (isset($this->cache['search'][$path][$ext][$prioritizeApp])) {
            return $this->cache['search'][$path][$ext][$prioritizeApp];
        }

        $foundPaths = $this->locator->search($path, $ext, $prioritizeApp);

        $this->cache['search'][$path][$ext][$prioritizeApp] = $foundPaths;
        $this->cacheUpdated                                 = true;

        return $foundPaths;
    }

    /**
     * Scans the defined namespaces, returning a list of all files
     * that are contained within the subpath specified by $path.
     *
     * @return list<string> List of file paths
     */
    public function listFiles(string $path): array
    {
        if (isset($this->cache['listFiles'][$path])) {
            return $this->cache['listFiles'][$path];
        }

        $files = $this->locator->listFiles($path);

        $this->cache['listFiles'][$path] = $files;
        $this->cacheUpdated              = true;

        return $files;
    }

    /**
     * Scans the provided namespace, returning a list of all files
     * that are contained within the sub path specified by $path.
     *
     * @return list<string> List of file paths
     */
    public function listNamespaceFiles(string $prefix, string $path): array
    {
        if (isset($this->cache['listNamespaceFiles'][$prefix][$path])) {
            return $this->cache['listNamespaceFiles'][$prefix][$path];
        }

        $files = $this->locator->listNamespaceFiles($prefix, $path);

        $this->cache['listNamespaceFiles'][$prefix][$path] = $files;
        $this->cacheUpdated                                = true;

        return $files;
    }

    /**
     * Attempts to locate a file by examining the name for a namespace
     * and looking through the PSR-4 namespaced files that we know about.
     *
     * @param string                $file   The relative file path or namespaced file to
     *                                      locate. If not namespaced, search in the app
     *                                      folder.
     * @param non-empty-string|null $folder The folder within the namespace that we should
     *                                      look for the file. If $file does not contain
     *                                      this value, it will be appended to the namespace
     *                                      folder.
     * @param string                $ext    The file extension the file should have.
     *
     * @return false|string The path to the file, or false if not found.
     */
    public function locateFile(string $file, ?string $folder = null, string $ext = 'php'): false|string
    {
        if (isset($this->cache['locateFile'][$file][$folder][$ext])) {
            return $this->cache['locateFile'][$file][$folder][$ext];
        }

        $files = $this->locator->locateFile($file, $folder, $ext);

        $this->cache['locateFile'][$file][$folder][$ext] = $files;
        $this->cacheUpdated                              = true;

        return $files;
    }
}
