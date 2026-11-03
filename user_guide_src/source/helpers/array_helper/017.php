<?php

$data = [
    'user' => [
        'profile' => [
            'id'   => 123,
            'name' => 'John',
        ],
    ],
];

// Returns: true
$removed = dot_array_unset($data, 'user.profile.id');

// Returns: false (path does not exist)
$removedAgain = dot_array_unset($data, 'user.profile.id');

$users = [
    ['id' => 1, 'name' => 'Jane'],
    ['id' => 2, 'name' => 'John'],
];

// Returns: true (removes "id" from all user rows)
$removedIds = dot_array_unset($users, '*.id');

// Returns: true (clears all keys under "user")
$clearedUser = dot_array_unset($data, 'user.*');
