<?php

$query = $db->query('YOUR QUERY');

$row = $query->getRowArray();

if (isset($row)) {
    echo $row['title'];
    echo $row['name'];
    echo $row['body'];
}
