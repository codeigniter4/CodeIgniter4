<?php

namespace Tests\Support\Autoloader;

use CodeIgniter\Autoloader\FileLocator;
use RuntimeException;

/**
 * Class FatalLocator
 *
 * A locator replacement designed to throw
 * exceptions when used to indicate when
 * a lookup actually happens.
 */
class FatalLocator extends FileLocator
{
    /**
     * Throws.
     *
     * @param string $file   The namespaced file to locate
     * @param string $folder The folder within the namespace that we should look for the file.
     * @param string $ext    The file extension the file should have.
     *
     * @return false|string The path to the file, or false if not found.
     */
    public function locateFile(string $file, ?string $folder = null, string $ext = 'php')
    {
        $folder = $folder ?? 'null';

        throw new RuntimeException("locateFile({$file}, {$folder}, {$ext})");
    }

    //--------------------------------------------------------------------

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
     * @param string $path
     * @param string $ext
     * @param bool   $prioritizeApp
     *
     * @return array
     */
    public function search(string $path, string $ext = 'php', bool $prioritizeApp = true): array
    {
        $prioritizeApp = $prioritizeApp ? 'true' : 'false';

        throw new RuntimeException("search({$path}, {$ext}, {$prioritizeApp})");
    }
}
