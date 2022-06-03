<?php

$query = $db->query('SELECT * FROM users;');

foreach ($query->getResult('User') as $user) {
    echo $user->name; // access attributes
    echo $user->reverseName(); // or methods defined on the 'User' class
}
