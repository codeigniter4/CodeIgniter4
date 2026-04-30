<?php

$result = $this->db->transaction(static function ($db) use ($order, $id) {
    $db->table('orders')->insert($order);
    $orderId = $db->insertID();

    $db->table('stock')->where('id', $id)->decrement('qty');

    $db->afterCommit(static function (): void {
        // Dispatch a job or send a notification after commit.
    });

    return $orderId;
});
