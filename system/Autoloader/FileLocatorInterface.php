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

/**
 * Allows loading non-class files in a namespaced manner.
 * Works with Helpers, Views, etc.
 */
interface FileLocatorInterface
{
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
    public function locateFile(string $file, ?string $folder = null, string $ext = 'php');

    /**
     * Examines a file and returns the fully qualified class name.
     */
    public function getClassname(string $file): string;

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
     */
    public function search(string $path, string $ext = 'php', bool $prioritizeApp = true): array;

    /**
     * Find the qualified name of a file according to
     * the namespace of the first matched namespace path.
     *
     * @return false|string The qualified name or false if the path is not found
     */
    public function findQualifiedNameFromPath(string $path);

    /**
     * Scans the defined namespaces, returning a list of all files
     * that are contained within the subpath specified by $path.
     *
     * @return list<string> List of file paths
     */
    public function listFiles(string $path): array;

    /**
     * Scans the provided namespace, returning a list of all files
     * that are contained within the sub path specified by $path.
     *
     * @return list<string> List of file paths
     */
    public function listNamespaceFiles(string $prefix, string $path): array;
}
