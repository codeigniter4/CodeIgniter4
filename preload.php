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
 *   1. Set Preload::$paths.
 *   2. Set opcache.preload in php.ini.
 *     php.ini:
 *     opcache.preload=/path/to/preload.php
 */

// Load the paths config file
require __DIR__ . '/app/Config/Paths.php';

// Path to the front controller
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

/**
 * See https://www.php.net/manual/en/function.str-contains.php#126277
 */
if (! function_exists('str_contains')) {
    /**
     * Polyfill of str_contains()
     */
    function str_contains(string $haystack, string $needle): bool
    {
        return empty($needle) || strpos($haystack, $needle) !== false;
    }
}

class Preload
{
    /**
     * @var array Paths to preload.
     */
    private array $paths = [
        [
            'include' => // __DIR__ . '/vendor/codeigniter4/framework/system',
                __DIR__ . '/system',
            'exclude' => [
                // Not needed if you don't use them.
                '/system/Database/OCI8/',
                '/system/Database/Postgre/',
                '/system/Database/SQLSRV/',
                // Not needed.
                '/system/Database/Seeder.php',
                '/system/Test/',
                '/system/Language/',
                '/system/CLI/',
                '/system/Commands/',
                '/system/Publisher/',
                '/system/ComposerScripts.php',
                '/Views/',
                // Errors occur.
                '/system/Config/Routes.php',
                '/system/ThirdParty/',
            ],
        ],
    ];

    public function __construct()
    {
        $this->loadAutoloader();
    }

    private function loadAutoloader()
    {
        $paths = new Config\Paths();
        require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
    }

    /**
     * Load PHP files.
     */
    public function load()
    {
        foreach ($this->paths as $path) {
            $directory = new RecursiveDirectoryIterator($path['include']);
            $fullTree  = new RecursiveIteratorIterator($directory);
            $phpFiles  = new RegexIterator(
                $fullTree,
                '/.+((?<!Test)+\.php$)/i',
                RecursiveRegexIterator::GET_MATCH
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

(new Preload())->load();
