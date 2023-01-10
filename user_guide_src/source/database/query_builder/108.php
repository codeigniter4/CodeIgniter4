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

$builder->upsertBatch($data);
// MySQLi  produces: INSERT INTO.. ON DUPLICATE KEY UPDATE..
// Postgre produces: INSERT INTO.. ON CONFLICT.. DO UPDATE..
// SQLite3 produces: INSERT INTO.. ON CONFLICT.. DO UPDATE..
// SQLSRV  produces: MERGE INTO.. WHEN MATCHED THEN UPDATE.. WHEN NOT MATCHED THEN INSERT..
// OCI8    produces: MERGE INTO.. WHEN MATCHED THEN UPDATE.. WHEN NOT MATCHED THEN INSERT..
