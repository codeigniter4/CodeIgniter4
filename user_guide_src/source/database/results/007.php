<?php

$query = $db->query('SELECT * FROM users LIMIT 1;');
$row   = $query->getRow(0, \App\Entities\User::class);

echo $row->name;           // access attributes
echo $row->reverse_name(); // or methods defined on the 'User' class
