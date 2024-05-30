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

require __DIR__ . '/../system/Test/bootstrap.php';

use CodeIgniter\CLI\CLI;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

function findExecutableFiles($dir)
{
    $execFileList = [
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

    // Check if the directory exists
    if (! is_dir($dir)) {
        throw new RuntimeException('No such directory: ' . $dir);
    }

    // Create a Recursive Directory Iterator
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir)
    );

    // Iterate over each item in the directory
    foreach ($iterator as $fileinfo) {
        // Check if the item is a file and is executable
        if ($fileinfo->isFile() && is_executable($fileinfo->getPathname())) {
            $filePath = $fileinfo->getPathname();

            // Check allow list
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

$dirs = ['admin', 'app', 'system', 'tests', 'user_guide_src', 'utils', 'writable'];

$executableFiles = [];

foreach ($dirs as $dir) {
    $executableFiles = array_merge($executableFiles, findExecutableFiles($dir));
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
