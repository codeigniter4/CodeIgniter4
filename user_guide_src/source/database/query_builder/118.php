<?php

$data = [
    [
        'order'   => 48372,
        'line'    => 3,
        'product' => 'Keyboard',
        'qty'     => 1,
    ],
    [
        'order'   => 48372,
        'line'    => 4,
        'product' => 'Mouse',
        'qty'     => 1,
    ],
    [
        'order'   => 48372,
        'line'    => 5,
        'product' => 'Monitor',
        'qty'     => 2,
    ],
];

$builder->setAlias('del')->where('del.qty >', 1)->deleteBatch($data, 'order, line');

// OR
$builder
    ->setData($data, true, 'del')
    ->onConstraint('order, line')
    ->where('del.qty >', 1)
    ->deleteBatch();

/*
 * MySQL Produces:
 * DELETE `order_line` FROM `order_line`
 * INNER JOIN (
 * SELECT 48372 `order`, 3 `line`, 'Keyboard' `product`, 1 `qty` UNION ALL
 * SELECT 48372 `order`, 4 `line`, 'Mouse'    `product`, 1 `qty` UNION ALL
 * SELECT 48372 `order`, 5 `line`, 'Monitor'  `product`, 2 `qty`
 * ) `del`
 * ON `order_line`.`order` = `del`.`order` AND `order_line`.`line` = `del`.`line`
 * WHERE `del`.`qty` > 1
 */
