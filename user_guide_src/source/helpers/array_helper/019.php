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

$except = dot_array_except($data, ['user.email', 'meta.request_id']);
/*
$except:
[
    'user' => [
        'id'   => 123,
        'name' => 'John',
    ],
    'meta' => [],
]
*/

$clearUser = dot_array_except($data, 'user.*');
/*
$clearUser:
[
    'user' => [],
    'meta' => ['request_id' => 'abc'],
]
*/
