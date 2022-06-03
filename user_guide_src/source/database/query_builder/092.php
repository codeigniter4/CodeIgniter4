<?php

$data = [
    [
        'title' => 'My title',
        'name'  => 'My Name 2',
        'date'  => 'My date 2',
    ],
    [
        'title' => 'Another title',
        'name'  => 'Another Name 2',
        'date'  => 'Another date 2',
    ],
];

$builder->updateBatch($data, 'title');
/*
 * Produces:
 * UPDATE `mytable` SET `name` = CASE
 * WHEN `title` = 'My title' THEN 'My Name 2'
 * WHEN `title` = 'Another title' THEN 'Another Name 2'
 * ELSE `name` END,
 * `date` = CASE
 * WHEN `title` = 'My title' THEN 'My date 2'
 * WHEN `title` = 'Another title' THEN 'Another date 2'
 * ELSE `date` END
 * WHERE `title` IN ('My title','Another title')
 */
