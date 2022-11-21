<?php

$query = $this->db->table('user2')
    ->select('user2.name, user2.email, user2.country')
    ->join('user', 'user.email = user2.email', 'inner')
    ->where('user2.country', 'US');

$additionalUpdateField = ['updated_at' => new RawSql('CURRENT_TIMESTAMP')];

$sql = $builder->table('user')
    ->setQueryAsData($query, null, 'u')
    ->onConstraint('email')
    ->updateFields($additionalUpdateField, true)
    ->updateBatch();
/*
 * Produces:
 * UPDATE `user`
 * INNER JOIN (
 * SELECT user2.name, user2.email, user2.country
 * FROM user2
 * INNER JOIN user ON user.email = user2.email
 * WHERE user2.country = 'US'
 * ) `u`
 * ON `user`.`email` = `u`.`email`
 * SET
 * `mytable`.`name` = `u`.`name`,
 * `mytable`.`email` = `u`.`email`,
 * `mytable`.`country` = `u`.`country`,
 * `mytable`.`updated_at` = CURRENT_TIMESTAMP()
 */
