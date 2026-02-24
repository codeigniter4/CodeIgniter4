<?php

$data = [
    'users' => [
        ['id' => 1, 'name' => 'Jane'],
        ['id' => 2, 'name' => 'John'],
    ],
];

// Returns: true (all matched users have an "id" key)
$hasIds = dot_array_has('users.*.id', $data);

// If any user is missing "id", this would return false.

// Returns: false
$hasEmails = dot_array_has('users.*.email', $data);
