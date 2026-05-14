<?php

$db->transaction(static function ($db) use ($accountId): void {
    $account = $db->table('accounts')
        ->where('id', $accountId)
        ->lockForUpdate()
        ->get()
        ->getRow();

    // Use $account to update the locked row safely...
});
