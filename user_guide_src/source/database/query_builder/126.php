<?php

use CodeIgniter\Database\JoinClause;

$builder->join('orders', static function (JoinClause $join): void {
    $join->on('orders.user_id', 'users.id')
        ->groupStart()
        ->where('orders.status', 'paid')
        ->orWhere('orders.status', 'pending')
        ->groupEnd();
});
