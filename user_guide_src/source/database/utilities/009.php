<?php

$db     = db_connect();
$dbutil = \CodeIgniter\Database\Config::utils();

$query = $db->query('SELECT * FROM mytable');

echo $dbutil->getCSVFromResult($query);
