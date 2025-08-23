<?php

use CodeIgniter\CLI\CLI;

// Main application loop
while ($this->isRunning()) {
    // Process work items
    $this->processNextItem();

    // Small delay to prevent CPU spinning
    usleep(100000); // 0.1 seconds
}

CLI::write('Process terminated gracefully.');
