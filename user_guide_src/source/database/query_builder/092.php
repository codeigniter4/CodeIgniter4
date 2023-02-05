<?php

$data = [
    [
        'title'  => 'Title 1',
        'author' => 'Author 1',
        'name'   => 'Name 1',
        'date'   => 'Date 1',
    ],
    [
        'title'  => 'Title 2',
        'author' => 'Author 2',
        'name'   => 'Name 2',
        'date'   => 'Date 2',
    ],
];
$builder->updateBatch($data, ['title', 'author']);
/*
 * Produces:
 * UPDATE `mytable`
 * INNER JOIN (
 * SELECT 'Title 1' `title`, 'Author 1' `author`, 'Name 1' `name`, 'Date 1' `date` UNION ALL
 * SELECT 'Title 2' `title`, 'Author 2' `author`, 'Name 2' `name`, 'Date 2' `date`
 * ) `u`
 * ON `mytable`.`title` = `u`.`title` AND `mytable`.`author` = `u`.`author`
 * SET
 * `mytable`.`title` = `u`.`title`,
 * `mytable`.`name` = `u`.`name`,
 * `mytable`.`date` = `u`.`date`
 */
