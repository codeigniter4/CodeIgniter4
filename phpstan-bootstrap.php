<?php

declare(strict_types = 1);

require __DIR__ . '/system/Test/bootstrap.php';

if (! defined('OCI_COMMIT_ON_SUCCESS')) {
    define('OCI_COMMIT_ON_SUCCESS', 32);
}
