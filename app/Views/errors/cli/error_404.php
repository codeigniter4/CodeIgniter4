<?php

use CodeIgniter\CLI\CLI;
use CodeIgniter\Debug\Exceptions;

if (Exceptions::isUsingPhpUnitErrorHandlingInSeparateProcess()) {
    return;
}

CLI::error('ERROR: ' . $code);
CLI::write($message);
CLI::newLine();
