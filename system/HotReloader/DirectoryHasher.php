<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HotReloader;

use CodeIgniter\Exceptions\FrameworkException;
use Config\Toolbar;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @internal
 */
final class DirectoryHasher
{
    /**
     * Generates an md5 value of all directories that are watched by the
     * Hot Reloader, as defined in the Config\Toolbar.
     *
     * This is the current app fingerprint.
     */
    public function hash(): string
    {
        return md5(implode('', $this->hashApp()));
    }

    /**
     * Generates an array of md5 hashes for all directories that are
     * watched by the Hot Reloader, as defined in the Config\Toolbar.
     */
    public function hashApp(): array
    {
        $hashes = [];

        $watchedDirectories = config(Toolbar::class)->watchedDirectories;

        foreach ($watchedDirectories as $directory) {
            if (is_dir(ROOTPATH . $directory)) {
                $hashes[$directory] = $this->hashDirectory(ROOTPATH . $directory);
            }
        }

        return array_unique(array_filter($hashes));
    }

    /**
     * Generates an md5 hash of a given directory and all of its files
     * that match the watched extensions defined in Config\Toolbar.
     */
    public function hashDirectory(string $path): string
    {
        if (! is_dir($path)) {
            throw FrameworkException::forInvalidDirectory($path);
        }

        $directory = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
        $filter    = new IteratorFilter($directory);
        $iterator  = new RecursiveIteratorIterator($filter);

        $hashes = [];

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $hashes[] = md5_file($file->getRealPath());
            }
        }

        return md5(implode('', $hashes));
    }
}
