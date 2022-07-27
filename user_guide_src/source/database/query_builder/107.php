<?php

$data = [
    'email'   => 'ahmadinejad@world.com',
    'name'    => 'Ahmadinejad',
    'country' => 'Iran',
];

$sql = $builder->set($data)->getCompiledUpsert();
echo $sql;
/* MySQLi  produces:
    INSERT INTO `db_user` (`country`, `email`, `name`)
    VALUES ('Iran','ahmadinejad@world.com','Ahmadinejad')
    ON DUPLICATE KEY UPDATE
    `country` = VALUES(`country`),
    `email` = VALUES(`email`),
    `name` = VALUES(`name`)
*/
