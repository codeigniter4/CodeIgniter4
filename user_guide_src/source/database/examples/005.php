<?php

$query = $db->query('SELECT name FROM my_table LIMIT 1');
$row   = $query->getRowArray();
echo $row['name'];
