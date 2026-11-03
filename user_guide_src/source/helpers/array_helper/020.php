<?php

$user = (object) ['id' => 123, 'name' => 'John'];

// Selecting the object itself keeps the object as the value.
$whole = dot_array_only(['user' => $user], 'user');
// ['user' => $user]

// Selecting a value inside the object builds the path with arrays.
$partial = dot_array_only(['user' => $user], 'user.id');
// ['user' => ['id' => 123]]
