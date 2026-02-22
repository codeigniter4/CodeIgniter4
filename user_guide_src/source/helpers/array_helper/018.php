<?php

$data = [
    'user' => [
        'id'    => 123,
        'name'  => 'John',
        'email' => 'john@example.com',
    ],
    'meta' => [
        'request_id' => 'abc',
    ],
];

$only = dot_array_only($data, ['user.id', 'meta.request_id']);
/*
$only:
[
    'user' => ['id' => 123],
    'meta' => ['request_id' => 'abc'],
]
*/

$userOnly = dot_array_only($data, 'user.*');
/*
$userOnly:
[
    'user' => [
        'id'    => 123,
        'name'  => 'John',
        'email' => 'john@example.com',
    ],
]
*/
