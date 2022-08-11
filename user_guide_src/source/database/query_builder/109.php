<?php

$data = [
    'id'      => 2,
    'email'   => 'ahmadinejad@world.com',
    'name'    => 'Ahmadinejad',
    'country' => 'Iran',
];

$builder->onConstraint('email')->upsert($data);
/* Postgre produces:
    INSERT INTO "db_user"("country", "email", "id", "name")
    VALUES ('Iran','ahmadinejad@world.com',2,'Ahmadinejad')
    ON CONFLICT("email")
    DO UPDATE SET
    "country" = "excluded"."country",
    "id" = "excluded"."id",
    "name" = "excluded"."name"
*/
