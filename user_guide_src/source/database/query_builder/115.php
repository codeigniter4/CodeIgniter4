<?php

$query = $this->db->table('user2')
    ->select('user2.name, user2.email, user2.country')
    ->join('user', 'user.email = user2.email', 'left')
    ->where('user.email IS NULL');

$additionalUpdateField = ['updated_at' => new RawSql('CURRENT_TIMESTAMP')];

$sql = $builder->setQueryAsData($query)
    ->onConstraint('email')
    ->updateFields($additionalUpdateField, true)
    ->upsertBatch();
/* MySQLi produces:
    INSERT INTO `db_user` (`country`, `email`, `name`)
    SELECT user2.name, user2.email, user2.country
    FROM user2
    LEFT JOIN user ON user.email = user2.email
    WHERE user.email IS NULL
    ON DUPLICATE KEY UPDATE
    `country` = VALUES(`country`),
    `email` = VALUES(`email`),
    `name` = VALUES(`name`),
    `updated_at` = CURRENT_TIMESTAMP
*/
