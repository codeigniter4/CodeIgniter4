<?php

use CodeIgniter\CLI\CLI;

$prompt = CLI::getPrompt();

// Returns one key, here 'MySQLi' when the user just presses enter.
$driver = $prompt->select('Which database driver?', [
    'MySQLi'  => 'MySQL / MariaDB',
    'Postgre' => 'PostgreSQL',
    'SQLite3' => 'SQLite',
], 'MySQLi');

// Returns key => label for each ticked option, possibly none.
$filters = $prompt->multiselect('Which filters should run on every request?', [
    'csrf'          => 'CSRF',
    'honeypot'      => 'Honeypot',
    'secureheaders' => 'Secure Headers',
], ['csrf']);

// Refuses to confirm until at least one option is ticked.
$environments = $prompt->multiselect('Which environments get the new key?', [
    'development' => 'Development',
    'testing'     => 'Testing',
    'production'  => 'Production',
], required: true);
