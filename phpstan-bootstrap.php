<?php

require __DIR__ . '/system/util_bootstrap.php';

defined('OCI_COMMIT_ON_SUCCESS') || define('OCI_COMMIT_ON_SUCCESS', 32);

foreach ([
    'app/Config',
] as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            require_once $file->getRealPath();
        }
    }
}
