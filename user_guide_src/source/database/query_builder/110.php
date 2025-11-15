<?php

$data = [
    'id'      => 2,
    'email'   => 'ahmadinejad@example.com',
    'name'    => 'Ahmadinejad Zaghari',
    'country' => 'Afghanistan',
];

$builder->updateFields('name, country')->setData($data, null, '_upsert')->upsert();
/* SQLSRV produces:
    MERGE INTO "test"."dbo"."db_user"
    USING (
     VALUES ('Iran','ahmadinejad@example.com',2,'Ahmadinejad')
    ) "_upsert" ("country", "email", "id", "name")
    ON ("test"."dbo"."db_user"."id" = "_upsert"."id")
    WHEN MATCHED THEN UPDATE SET
    "country" = "_upsert"."country",
    "name" = "_upsert"."name"
    WHEN NOT MATCHED THEN INSERT ("country", "email", "id", "name")
    VALUES ("_upsert"."country", "_upsert"."email", "_upsert"."id", "_upsert"."name");
*/
