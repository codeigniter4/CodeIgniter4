<?php

use CodeIgniter\Events\Events;

$orderId = $this->db->transaction(static function ($db) use ($order): int {
    $db->table('orders')->insert($order);
    $insertedOrderId = $db->insertID();

    $db->afterCommit(static function () use ($insertedOrderId): void {
        service('cache')->delete('orders_list');
        Events::trigger('order_created', $insertedOrderId);

        // Dispatch a queued job or send a notification here.
        // The new order is committed and visible to other database connections.
    });

    return $insertedOrderId;
});

if ($orderId === false) {
    // Handle the transaction failure.
}
