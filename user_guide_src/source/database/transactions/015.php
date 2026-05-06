<?php

use CodeIgniter\Database\Exceptions\DatabaseException;

try {
    $result = $db->transException(true)->transaction(static function ($db) {
        $db->table('orders')->insert($order);

        return $db->insertID();
    });
} catch (DatabaseException $e) {
    if ($db->isRetryableTransactionException($e)) {
        // Retry the whole transaction according to your application's policy.
    }

    throw $e;
}
