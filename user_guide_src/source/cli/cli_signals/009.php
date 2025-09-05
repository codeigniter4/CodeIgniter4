<?php

use CodeIgniter\CLI\CLI;

// Check for termination before expensive operations
if ($this->shouldTerminate()) {
    CLI::write('Termination requested, skipping file processing.');

    return;
}

// Process large file
foreach ($largeDataSet as $item) {
    // Check periodically during long operations
    if ($this->shouldTerminate()) {
        CLI::write('Termination requested during processing.');
        break;
    }

    $this->processItem($item);
}
