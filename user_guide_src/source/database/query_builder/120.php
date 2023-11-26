<?php

use CodeIgniter\Database\RawSql;

$builder->setData($data)->onConstraint('title, author')->updateBatch();

// OR
$builder->setData($data, null, 'u')
    ->onConstraint(['`mytable`.`title`' => '`u`.`title`', 'author' => new RawSql('`u`.`author`')])
    ->updateBatch();

// OR
foreach ($data as $row) {
    $builder->setData($row);
}
$builder->onConstraint('title, author')->updateBatch();

// OR
$builder->setData($data, true, 'u')
    ->onConstraint(new RawSql('`mytable`.`title` = `u`.`title` AND `mytable`.`author` = `u`.`author`'))
    ->updateFields(['last_update' => new RawSql('CURRENT_TIMESTAMP()')], true)
    ->updateBatch();
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
 * `mytable`.`date` = `u`.`date`,
 * `mytable`.`last_update` = CURRENT_TIMESTAMP() // this only applies to the last scenario
 */
