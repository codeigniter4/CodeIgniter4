<?php

$db     = db_connect();
$dbutil = \CodeIgniter\Database\Config::utils();

$query = $db->query('SELECT * FROM mytable');

$delimiter = ',';
$newline   = "\r\n";
$enclosure = '"';

echo $dbutil->getCSVFromResult($query, $delimiter, $newline, $enclosure);
