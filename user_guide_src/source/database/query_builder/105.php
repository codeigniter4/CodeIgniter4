<?php

$data = [
    [
        'id' => 1,
        'name'  => 'My Name',
        'date'  => 'My date',
    ],
    [
        'id' => 2,
        'name'  => 'Another Name',
        'date'  => 'Another date',
    ],
];

$builder->replaceBatch($data);
// Produces: INSERT INTO mytable (id, name, date) VALUES (1, 'My name', 'My date'),  (2, 'Another name', 'Another date') ON DUPLICATE KEY UPDATE id = VALUES(id), name = VALUES(name), date = VALUES(date)
