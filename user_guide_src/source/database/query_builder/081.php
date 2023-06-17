<?php

$data = [
    [
        'title' => 'My title',
        'name'  => 'My Name',
        'date'  => 'My date',
    ],
    [
        'title' => 'Another title',
        'name'  => 'Another Name',
        'date'  => 'Another date',
    ],
];

$builder->insertBatch($data);
/*
 * Produces:
 * INSERT INTO mytable (title, name, date)
 *      VALUES ('My title', 'My name', 'My date'),
 *      ('Another title', 'Another name', 'Another date')
 */
