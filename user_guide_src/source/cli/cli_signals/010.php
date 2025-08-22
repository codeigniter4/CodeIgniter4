<?php

use CodeIgniter\CLI\CLI;

// Request termination based on conditions
if ($errorCount > $this->maxErrors) {
    CLI::write("Too many errors ({$errorCount}), requesting termination.", 'red');
    $this->requestTermination();

    return;
}
