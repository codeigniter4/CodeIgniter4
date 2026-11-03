<?php

use CodeIgniter\CLI\Commands;

/** @var Commands $commands */
$commands = service('commands');

if ($commands->hasLegacyCommand('foo') && $commands->hasModernCommand('foo')) {
    // Both registries claim the name; the legacy version will run.
}
