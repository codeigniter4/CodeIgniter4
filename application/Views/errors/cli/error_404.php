<?php declare(strict_types=1);
use CodeIgniter\CLI\CLI;

CLI::error('ERROR: ' . $code);
CLI::write($message);
CLI::newLine();
