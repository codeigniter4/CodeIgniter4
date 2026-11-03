<?php

$query = $db->query('SELECT name FROM my_table LIMIT 1');
$row   = $query->getRow();
echo $row->name;
