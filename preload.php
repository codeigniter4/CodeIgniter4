<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

/*
 *---------------------------------------------------------------
 * Sample file for Preloading
 *---------------------------------------------------------------
 * See https://www.php.net/manual/en/opcache.preloading.php
 *
 * How to Use:
 *   0. Copy this file to your project root folder.
 *   1. Set the $paths property of the preload class below.
 *   2. Set opcache.preload in php.ini.
 *     php.ini:
 *     opcache.preload=/path/to/preload.php
 */

// Load the paths config file
require __DIR__ . '/app/Config/Paths.php';

// Path to the front controller
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

class preload
{
    /**
     * @var array Paths to preload.
     */
    private array $paths = [
        [
            'include' => __DIR__ . '/vendor/codeigniter4/framework/system', // Change this path if using manual installation
            'exclude' => [
                // Not needed if you don't use them.
                '/system/Database/OCI8/',
                '/system/Database/Postgre/',
                '/system/Database/SQLite3/',
                '/system/Database/SQLSRV/',
                // Not needed for web apps.
                '/system/Database/Seeder.php',
                '/system/Test/',
                '/system/CLI/',
                '/system/Commands/',
                '/system/Publisher/',
                '/system/ComposerScripts.php',
                // Not Class/Function files.
                '/system/Config/Routes.php',
                '/system/Language/',
                '/system/bootstrap.php',
                '/system/rewrite.php',
                '/Views/',
                // Errors occur.
                '/system/ThirdParty/',
            ],
        ],
    ];

    public function __construct()
    {
        $this->loadAutoloader();
    }

    private function loadAutoloader(): void
    {
        $paths = new Config\Paths();
        require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'Boot.php';

        CodeIgniter\Boot::preload($paths);
    }

    /**
     * Load PHP files.
     */
    public function load(): void
    {
        foreach ($this->paths as $path) {
            $directory = new RecursiveDirectoryIterator($path['include']);
            $fullTree  = new RecursiveIteratorIterator($directory);
            $phpFiles  = new RegexIterator(
                $fullTree,
                '/.+((?<!Test)+\.php$)/i',
                RecursiveRegexIterator::GET_MATCH,
            );

            foreach ($phpFiles as $key => $file) {
                foreach ($path['exclude'] as $exclude) {
                    if (str_contains($file[0], $exclude)) {
                        continue 2;
                    }
                }

                require_once $file[0];
                echo 'Loaded: ' . $file[0] . "\n";
            }
        }
    }
}

(new preload())->load();
