<?php

$data = [
    'title' => $title,
    'name'  => $name,
    'date'  => $date,
];

$builder->where('id', $id);
$builder->update($data);
/*
 * Produces:
 * UPDATE mytable
 * SET title = '{$title}', name = '{$name}', date = '{$date}'
 * WHERE id = $id
 */
