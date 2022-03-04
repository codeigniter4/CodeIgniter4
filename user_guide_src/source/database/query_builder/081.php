<?php

$data = [
    'title' => 'My title',
    'name'  => 'My Name',
    'date'  => 'My date',
];

$builder->replace($data);
// Executes: REPLACE INTO mytable (title, name, date) VALUES ('My title', 'My name', 'My date')
