<?php

use CodeIgniter\Database\BaseBuilder;

// With closure
$builder->whereExists(static function (BaseBuilder $builder) {
    $builder->select('1', false)
        ->from('orders')
        ->whereColumn('orders.user_id', 'users.id');
});
// Produces: WHERE EXISTS (SELECT 1 FROM "orders" WHERE "orders"."user_id" = "users"."id")

// With builder directly
$subQuery = $db->table('orders')->select('1', false)->whereColumn('orders.user_id', 'users.id');
$builder->whereNotExists($subQuery);
