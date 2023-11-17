<?php

$query = $db->query('YOUR QUERY');

$rows = $query->getCustomResultObject('User');

foreach ($rows as $row) {
    echo $row->id;
    echo $row->email;
    echo $row->lastLogin('Y-m-d');
}
