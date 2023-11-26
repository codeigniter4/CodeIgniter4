<?php

use CodeIgniter\Database\RawSql;

$sql = 'user.id = device.user_id AND ((1=1 OR 1=1) OR (1=1 OR 1=1))';
$builder->join('user', new RawSql($sql), 'LEFT');
// Produces: LEFT JOIN "user" ON user.id = device.user_id AND ((1=1 OR 1=1) OR (1=1 OR 1=1))
