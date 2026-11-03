<?php

$lock = service('locks')->create('exports.monthly', 300);

if ($lock->acquire()) {
    queue_export_job($lock->owner());
}

// Later, in another process:
$restored = service('locks')->restore('exports.monthly', $owner);
$restored->release();
