<?php

$data = [
    'title' => 'My title',
    'name'  => 'My Name',
    'date'  => 'My date',
];

$builder->ignore(true)->insert($data);
// Produces: INSERT OR IGNORE INTO mytable (title, name, date) VALUES ('My title', 'My name', 'My date')
