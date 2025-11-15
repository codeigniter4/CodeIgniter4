<?php

use CodeIgniter\Database\RawSql;

$sql    = "CONCAT(users.name, ' ', IF(users.surname IS NULL OR users.surname = '', '', users.surname))";
$rawSql = new RawSql($sql);
$builder->like($rawSql, 'value', 'both');
