<?php

$query = $db->query('YOUR QUERY');

$row = $query->getCustomRowObject(0, 'User');

if (isset($row)) {
    echo $row->email;               // access attributes
    echo $row->last_login('Y-m-d'); // access class methods
}
