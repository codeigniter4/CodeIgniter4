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

namespace Utils;

require __DIR__ . '/../system/util_bootstrap.php';

use CodeIgniter\CLI\CLI;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

/**
 * @param list<string> $excludeDirs
 *
 * @return list<string>
 */
function findExecutableFiles(string $dir, array $excludeDirs = []): array
{
    static $execFileList = [
        '.github/scripts/deploy-userguide',
        'admin/release-userguide',
        'admin/release-deploy',
        'admin/apibot',
        'admin/alldocs',
        'admin/release',
        'admin/docbot',
        'admin/release-notes.bb',
        'admin/release-revert',
        'admin/starter/builds',
        'user_guide_src/add-edit-this-page',
    ];

    $executableFiles = [];

    if (! is_dir($dir)) {
        throw new RuntimeException('No such directory: ' . $dir);
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO),
        RecursiveIteratorIterator::CHILD_FIRST | RecursiveIteratorIterator::LEAVES_ONLY,
    );

    /** @var SplFileInfo $fileinfo */
    foreach ($iterator as $fileinfo) {
        $filePath = $fileinfo->getPathname();

        if ($fileinfo->isFile() && is_executable($filePath)) {
            $dirPath = dirname($filePath);

            foreach ($excludeDirs as $excludeDir) {
                if (str_contains($dirPath, $excludeDir)) {
                    continue 2;
                }
            }

            if (in_array($filePath, $execFileList, true)) {
                continue;
            }

            if (str_ends_with($filePath, '.sh')) {
                continue;
            }

            $executableFiles[] = $filePath;
        }
    }

    return $executableFiles;
}

// Main
chdir(__DIR__ . '/../');

$includeDirs = ['.github', 'admin', 'app', 'public', 'system', 'tests', 'user_guide_src', 'utils', 'writable'];
$excludeDirs = ['utils/vendor'];

$executableFiles = [];

foreach ($includeDirs as $dir) {
    $executableFiles = array_merge($executableFiles, findExecutableFiles($dir, $excludeDirs));
}

if ($executableFiles !== []) {
    CLI::write('Files with unnecessary execution permissions were detected:', 'light_gray', 'red');

    foreach ($executableFiles as $file) {
        CLI::write('- ' . $file);
    }

    exit(1);
}

CLI::write('No files with unnecessary execution permissions were detected.', 'black', 'green');

exit(0);
