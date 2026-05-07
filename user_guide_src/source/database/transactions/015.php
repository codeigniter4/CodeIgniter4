<?php

use CodeIgniter\Database\Exceptions\RetryableTransactionException;

try {
    $result = $db->transException(true)->transaction(static function ($db) {
        $db->table('orders')->insert($order);

        return $db->insertID();
    });
} catch (RetryableTransactionException $e) {
    // Retry the whole transaction according to your application's policy.
    throw $e;
}
