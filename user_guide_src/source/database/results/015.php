<?php

$query = $db->query('YOUR QUERY');

$row = $query->getCustomRowObject(0, \App\Entities\User::class);

if (isset($row)) {
    echo $row->email;              // access attributes
    echo $row->lastLogin('Y-m-d'); // access class methods
}
