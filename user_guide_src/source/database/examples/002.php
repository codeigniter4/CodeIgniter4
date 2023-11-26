<?php

$query   = $db->query('SELECT name, title, email FROM my_table');
$results = $query->getResult();

foreach ($results as $row) {
    echo $row->title;
    echo $row->name;
    echo $row->email;
}

echo 'Total Results: ' . count($results);
