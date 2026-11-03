<?php

use CodeIgniter\Database\RawSql;

$builder->join(
    new RawSql('(SELECT user_id, MAX(created_at) AS latest FROM posts GROUP BY user_id) recent'),
    'recent.user_id = users.id',
    'LEFT',
);
