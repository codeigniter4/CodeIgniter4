<?php

// With closure

$builder->where('advance_amount <', function (BaseBuilder $builder) {
    return $builder->select('MAX(advance_amount)', false)->from('orders')->where('id >', 2);
});

// Produces: WHERE "advance_amount" < (SELECT MAX(advance_amount) FROM "orders" WHERE "id" > 2)

// With builder directly
$subQuery = $db->table('orders')->select('MAX(advance_amount)', false)->where('id >', 2)
$builder->where('advance_amount <', $subQuery);
