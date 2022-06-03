<?php

$query = $thisdb->query('SELECT title FROM my_table');

foreach ($query->getResult() as $row) {
    echo $row->title;
}

$query->freeResult(); // The $query result object will no longer be available

$query2 = $db->query('SELECT name FROM some_table');

$row = $query2->getRow();
echo $row->name;
$query2->freeResult(); // The $query2 result object will no longer be available
