<?php

$query = "SELECT user2.name, user2.email, user2.country
          FROM user2
          LEFT JOIN user ON user.email = user2.email
          WHERE user.email IS NULL";

$sql = $builder->ignore(true)->fromQuery($query)->insertBatch();
/* MySQLi produces:
    INSERT IGNORE INTO `db_user` (`name`, `country`, `email`)
    SELECT user2.name, user2.email, user2.country
    FROM user2
    LEFT JOIN user ON user.email = user2.email
    WHERE user.email IS NULL
*/
