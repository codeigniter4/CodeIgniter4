<?php

$result = $this->db->transaction(static function ($db) use ($order) {
    $db->table('orders')->insert($order);

    return $db->insertID();
}, attempts: 3);
