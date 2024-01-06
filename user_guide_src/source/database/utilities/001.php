<?php

$db     = db_connect();
$dbutil = \Config\Database::utils();

$query = $db->query('SELECT * FROM mytable');

$config = [
    'root'    => 'root',
    'element' => 'element',
    'newline' => "\n",
    'tab'     => "\t",
];

echo $dbutil->getXMLFromResult($query, $config);
