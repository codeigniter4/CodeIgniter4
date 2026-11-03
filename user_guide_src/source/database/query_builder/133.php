<?php

$db->transaction(static function ($db) use ($accountId): void {
    $account = $db->table('accounts')
        ->where('id', $accountId)
        ->lockForUpdate()
        ->nowait()
        ->get()
        ->getRow();

    // Use $account while preventing concurrent transactions from modifying it...
});
