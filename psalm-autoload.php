<?php

declare(strict_types=1);

require __DIR__ . '/system/util_bootstrap.php';

$directories = [
    'system/Helpers',
    'tests/_support',
    'tests/system/Config/fixtures',
];
$excludeDirs = [
    'tests/_support/View/Cells',
    'tests/_support/View/Views',
];
$excludeFiles = [
    'tests/_support/Config/Filters.php',
    'tests/_support/Config/Routes.php',
];

foreach ($directories as $directory) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $directory,
            RecursiveDirectoryIterator::UNIX_PATHS | RecursiveDirectoryIterator::CURRENT_AS_FILEINFO,
        ),
        RecursiveIteratorIterator::CHILD_FIRST,
    );

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }

        if (in_array($file->getPath(), $excludeDirs, true)) {
            continue;
        }

        if ($file->getExtension() !== 'php') {
            continue;
        }

        if (in_array($file->getPathname(), $excludeFiles, true)) {
            continue;
        }

        require_once $file->getPathname();
    }
}
