<?php

use CodeIgniter\Database\RawSql;

$data = [
    'id'          => new RawSql('DEFAULT'),
    'title'       => 'My title',
    'name'        => 'My Name',
    'date'        => '2022-01-01',
    'last_update' => new RawSql('CURRENT_TIMESTAMP()'),
];

$builder->insert($data);
/* Produces:
    INSERT INTO mytable (id, title, name, date, last_update)
    VALUES (DEFAULT, 'My title', 'My name', '2022-01-01', CURRENT_TIMESTAMP())
*/
