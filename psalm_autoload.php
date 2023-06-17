<?php

declare(strict_types=1);

require __DIR__ . '/system/Test/bootstrap.php';

$helperDirs = [
    'system/Helpers',
];

foreach ($helperDirs as $dir) {
    $dir = __DIR__ . '/' . $dir;
    if (! is_dir($dir)) {
        continue;
    }

    chdir($dir);

    foreach (glob('*_helper.php') as $filename) {
        $filePath = realpath($dir . '/' . $filename);

        require_once $filePath;
    }
}

$dirs = [
    'tests/_support/Controllers',
];

foreach ($dirs as $dir) {
    $dir = __DIR__ . '/' . $dir;
    if (! is_dir($dir)) {
        continue;
    }

    chdir($dir);

    foreach (glob('*.php') as $filename) {
        $filePath = realpath($dir . '/' . $filename);

        require_once $filePath;
    }
}

chdir(__DIR__);
