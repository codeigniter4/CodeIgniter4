<?php

$lock = service('locks')->create('reports.daily-export', 300);

if (! $lock->acquire()) {
    return;
}

try {
    // Run the work that must not overlap.
} finally {
    $lock->release();
}
