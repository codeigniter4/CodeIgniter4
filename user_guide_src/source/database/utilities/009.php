<?php

$db     = db_connect();
$dbutil = \Config\Database::utils();

$query = $db->query('SELECT * FROM mytable');

echo $dbutil->getCSVFromResult($query);
