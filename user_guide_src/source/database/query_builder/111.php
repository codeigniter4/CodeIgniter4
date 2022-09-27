<?php

$data = [
    [
        'id'      => 2,
        'email'   => 'ahmadinejad@example.com',
        'name'    => 'Ahmadinejad',
        'country' => 'Iran',
    ],
    [
        'id'      => null,
        'email'   => 'pedro@example.com',
        'name'    => 'Pedro',
        'country' => 'El Salvador',
    ],
];

$additionalUpdateField = ['updated_at' => new RawSql('CURRENT_TIMESTAMP')];

$sql = $builder->setData($data)->updateFields($additionalUpdateField, true)->upsertBatch();
/* MySQLi produces:
    INSERT INTO `db_user` (`country`, `email`, `name`)
    VALUES ('Iran','ahmadinejad@example.com','Ahmadinejad'),('El Salvador','pedro@example.com','Pedro')
    ON DUPLICATE KEY UPDATE
    `country` = VALUES(`country`),
    `email` = VALUES(`email`),
    `name` = VALUES(`name`),
    `updated_at` = CURRENT_TIMESTAMP
*/
