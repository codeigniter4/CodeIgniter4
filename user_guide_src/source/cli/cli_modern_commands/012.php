<?php

use CodeIgniter\CLI\CLI;

// Inside execute():

// Run `cache:clear` without leaking its own output; emit our own message instead.
$exitCode = $this->callSilently('cache:clear');

if ($exitCode === EXIT_SUCCESS) {
    CLI::write('Cache cleared as part of deploy step.', 'green');
}
