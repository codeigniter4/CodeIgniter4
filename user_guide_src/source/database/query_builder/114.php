<?php

$data = [
    'email'   => 'ahmadinejad@example.com',
    'name'    => 'Ahmadinejad',
    'country' => 'Iran',
];

$sql = $builder->setData($data)->getCompiledUpsert();
echo $sql;
/* MySQLi produces:
    INSERT INTO `db_user` (`country`, `email`, `name`)
    VALUES ('Iran','ahmadinejad@example.com','Ahmadinejad')
    ON DUPLICATE KEY UPDATE
    `country` = VALUES(`country`),
    `email` = VALUES(`email`),
    `name` = VALUES(`name`)
*/
