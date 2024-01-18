<?php

$data = [
    [
        'name'                  => 'Derek Jones',
        'country'               => 'Greece',
        'updated_at::TIMESTAMP' => '2023-12-02 18:47:52',
    ],
    [
        'name'                  => 'Ahmadinejad',
        'country'               => 'Greece',
        'updated_at::TIMESTAMP' => '2023-12-02 18:47:52',
    ],
];
$builder->updateBatch($data, 'name');
/*
 * Produces:
 * UPDATE "db_user"
 * SET
 * "country" = _u."country",
 * "updated_at" = _u."updated_at"
 * FROM (
 * SELECT 'Greece' "country", 'Derek Jones' "name", '2023-12-02 18:47:52'::TIMESTAMP "updated_at" UNION ALL
 * SELECT 'Greece' "country", 'Ahmadinejad' "name", '2023-12-02 18:47:52'::TIMESTAMP "updated_at"
 * ) _u
 * WHERE "db_user"."name" = _u."name"
 */
