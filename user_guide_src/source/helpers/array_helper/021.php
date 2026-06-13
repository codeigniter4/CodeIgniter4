<?php

$user = (object) ['id' => 123, 'name' => 'John'];

// An untouched object is kept as it is.
$untouched = dot_array_except(['user' => $user], 'meta.id');
// ['user' => $user]

// Removing a key from inside an object returns that part as an array.
$partial = dot_array_except(['user' => $user], 'user.id');
// ['user' => ['name' => 'John']]
