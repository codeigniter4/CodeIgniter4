<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * This class is used by Composer during installs and updates
 * to move files to locations within the system folder so that end-users
 * do not need to use Composer to install a package, but can simply
 * download.
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class ComposerScripts
{
    /**
     * Path to the ThirdParty directory.
     *
     * @var string
     */
    private static $path = __DIR__ . '/ThirdParty/';

    /**
     * Direct dependencies of CodeIgniter to copy
     * contents to `system/ThirdParty/`.
     *
     * @var array<string, array<string, string>>
     */
    private static $dependencies = [
        'kint-src' => [
            'from' => __DIR__ . '/../vendor/kint-php/kint/src/',
            'to'   => __DIR__ . '/ThirdParty/Kint/',
        ],
        'kint-resources' => [
            'from' => __DIR__ . '/../vendor/kint-php/kint/resources/',
            'to'   => __DIR__ . '/ThirdParty/Kint/resources/',
        ],
        'escaper' => [
            'from' => __DIR__ . '/../vendor/laminas/laminas-escaper/src/',
            'to'   => __DIR__ . '/ThirdParty/Escaper/',
        ],
        'psr-log' => [
            'from' => __DIR__ . '/../vendor/psr/log/Psr/Log/',
            'to'   => __DIR__ . '/ThirdParty/PSR/Log/',
        ],
    ];

    /**
     * This static method is called by Composer after every update event,
     * i.e., `composer install`, `composer update`, `composer remove`.
     *
     * @return void
     */
    public static function postUpdate()
    {
        self::recursiveDelete(self::$path);

        foreach (self::$dependencies as $dependency) {
            self::recursiveMirror($dependency['from'], $dependency['to']);
        }

        self::copyKintInitFiles();
        self::recursiveDelete(self::$dependencies['psr-log']['to'] . 'Test/');
    }

    /**
     * Recursively remove the contents of the previous `system/ThirdParty`.
     *
     * @param string $directory
     *
     * @return void
     */
    private static function recursiveDelete(string $directory): void
    {
        if (! is_dir($directory)) {
            echo sprintf('Cannot recursively delete "%s" as it does not exist.', $directory);
        }

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(rtrim($directory, '\\/'), FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        ) as $file) {
            $path = $file->getPathname();

            if ($file->isDir()) {
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }
    }

    /**
     * Recursively copy the files and directories of the origin directory
     * into the target directory, i.e. "mirror" its contents.
     *
     * @param string $originDir
     * @param string $targetDir
     *
     * @return void
     */
    private static function recursiveMirror(string $originDir, string $targetDir): void
    {
        $originDir = rtrim($originDir, '\\/');
        $targetDir = rtrim($targetDir, '\\/');

        if (! is_dir($originDir)) {
            echo sprintf('The origin directory "%s" was not found.', $originDir);

            exit(1);
        }

        if (is_dir($targetDir)) {
            echo sprintf('The target directory "%s" is existing. Run %s::recursiveDelete(\'%s\') first.', $targetDir, self::class, $targetDir);

            exit(1);
        }

        @mkdir($targetDir, 0755, true);

        $dirLen = strlen($originDir);

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($originDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        ) as $file) {
            $origin = $file->getPathname();
            $target = $targetDir . substr($origin, $dirLen);

            if ($file->isDir()) {
                @mkdir($target, 0755);
            } else {
                @copy($origin, $target);
            }
        }
    }

    /**
     * Copy Kint's init files into `system/ThirdParty/Kint/`
     *
     * @return void
     */
    private static function copyKintInitFiles(): void
    {
        $originDir = self::$dependencies['kint-src']['from'] . '../';
        $targetDir = self::$dependencies['kint-src']['to'];

        foreach (['init.php', 'init_helpers.php'] as $kintInit) {
            @copy($originDir . $kintInit, $targetDir . $kintInit);
        }
    }
}
