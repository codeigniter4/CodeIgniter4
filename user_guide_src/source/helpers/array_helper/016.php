<?php

$data = [];

dot_array_set($data, 'user.profile.id', 123);
dot_array_set($data, 'user.profile.name', 'John');

$users = [
    ['name' => 'Jane'],
    ['name' => 'John'],
];

dot_array_set($users, '*.active', true);

/*
$data is now:
[
    'user' => [
        'profile' => [
            'id'   => 123,
            'name' => 'John',
        ],
    ],
]
*/

/*
$users is now:
[
    ['name' => 'Jane', 'active' => true],
    ['name' => 'John', 'active' => true],
]
*/
