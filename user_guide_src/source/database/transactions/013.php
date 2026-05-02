<?php

use CodeIgniter\Events\Events;

$orderId = $this->db->transaction(static function ($db) use ($order): int {
    $db->table('orders')->insert($order);
    $orderId = $db->insertID();

    $db->afterCommit(static function () use ($orderId): void {
        service('cache')->delete('orders_list');
        Events::trigger('order_created', $orderId);

        // Dispatch a queued job or send a notification here.
        // The new order is committed and visible to other database connections.
    });

    return $orderId;
});
