<?php

$db->transaction(static function ($db): void {
    $jobs = $db->table('jobs')
        ->where('status', 'pending')
        ->orderBy('id', 'ASC')
        ->limit(10)
        ->lockForUpdate()
        ->skipLocked()
        ->get()
        ->getResultArray();

    // Mark or update the claimed jobs before committing the transaction...
});
