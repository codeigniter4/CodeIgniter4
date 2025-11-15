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
     */
    private static string $path = __DIR__ . '/ThirdParty/';

    /**
     * Direct dependencies of CodeIgniter to copy
     * contents to `system/ThirdParty/`.
     *
     * @var array<string, array<string, string>>
     */
    private static array $dependencies = [
        'kint-src' => [
            'license' => __DIR__ . '/../vendor/kint-php/kint/LICENSE',
            'from'    => __DIR__ . '/../vendor/kint-php/kint/src/',
            'to'      => __DIR__ . '/ThirdParty/Kint/',
        ],
        'kint-resources' => [
            'from' => __DIR__ . '/../vendor/kint-php/kint/resources/',
            'to'   => __DIR__ . '/ThirdParty/Kint/resources/',
        ],
        'escaper' => [
            'license' => __DIR__ . '/../vendor/laminas/laminas-escaper/LICENSE.md',
            'from'    => __DIR__ . '/../vendor/laminas/laminas-escaper/src/',
            'to'      => __DIR__ . '/ThirdParty/Escaper/',
        ],
        'psr-log' => [
            'license' => __DIR__ . '/../vendor/psr/log/LICENSE',
            'from'    => __DIR__ . '/../vendor/psr/log/src/',
            'to'      => __DIR__ . '/ThirdParty/PSR/Log/',
        ],
    ];

    /**
     * This static method is called by Composer after every update event,
     * i.e., `composer install`, `composer update`, `composer remove`.
     */
    public static function postUpdate(): void
    {
        self::recursiveDelete(self::$path);

        foreach (self::$dependencies as $key => $dependency) {
            // Kint may be removed.
            if (! is_dir($dependency['from']) && str_starts_with($key, 'kint')) {
                continue;
            }

            self::recursiveMirror($dependency['from'], $dependency['to']);

            if (isset($dependency['license'])) {
                $license = basename($dependency['license']);
                copy($dependency['license'], $dependency['to'] . '/' . $license);
            }
        }

        self::copyKintInitFiles();
    }

    /**
     * Recursively remove the contents of the previous `system/ThirdParty`.
     */
    private static function recursiveDelete(string $directory): void
    {
        if (! is_dir($directory)) {
            echo sprintf('Cannot recursively delete "%s" as it does not exist.', $directory) . PHP_EOL;

            return;
        }

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(rtrim($directory, '\\/'), FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
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

        if (! @mkdir($targetDir, 0755, true)) {
            echo sprintf('Cannot create the target directory: "%s"', $targetDir) . PHP_EOL;

            exit(1);
        }

        $dirLen = strlen($originDir);

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($originDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
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
