<?php

declare(strict_types=1);

require __DIR__ . '/system/util_bootstrap.php';

$directories = [
    'system/Helpers',
    'tests/_support',
    'tests/system/Config/fixtures',
];
$excludeDirs = [
    'tests/_support/Config',
    'tests/_support/View/Cells',
    'tests/_support/View/Views',
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

        require_once $file->getPathname();
    }
}
