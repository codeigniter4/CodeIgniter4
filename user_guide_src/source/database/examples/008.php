<?php

$data = [
    'title' => $title,
    'name'  => $name,
    'date'  => $date,
];

$db->table('mytable')->insert($data);
// Produces: INSERT INTO mytable (title, name, date) VALUES ('{$title}', '{$name}', '{$date}')
