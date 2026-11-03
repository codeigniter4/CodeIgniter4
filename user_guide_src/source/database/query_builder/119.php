<?php

use CodeIgniter\Database\RawSql;

$query = $this->db->table('user2')->select('email, name, country')->where('country', 'Greece');

$this->db->table('user')
    ->setQueryAsData($query, 'alias')
    ->onConstraint('email')
    ->where('alias.name = user.name')
    ->deleteBatch();

/* MySQLi produces:
    DELETE `user` FROM `user`
    INNER JOIN (
    SELECT `email`, `name`, `country`
    FROM `user2`
    WHERE `country` = 'Greece') `alias`
    ON `user`.`email` = `alias`.`email`
    WHERE `alias`.`name` = `user`.`name`
*/
