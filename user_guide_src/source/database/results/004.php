<?php

$query = $db->query('YOUR QUERY');

foreach ($query->getResultArray() as $row) {
    echo $row['title'];
    echo $row['name'];
    echo $row['body'];
}
