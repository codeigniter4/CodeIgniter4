<?php

use Config\Services;

$validation = Services::validation();

$data = [
    'contacts' => [
        'name' => 'Joe Smith',
        'just' => [
            'friends' => [
                ['name' => 'SATO Taro'],
                ['name' => 'Li Ming'],
                ['name' => 'Heinz Müller'],
            ],
        ],
    ],
];

$validation->setRules(
    ['contacts.*.name' => 'required|max_length[8]']
);

$validation->run($data); // false

d($validation->getErrors());
/*
 Before: Captured `contacts.*.*.*.name` incorrectly.
 [
   contacts.just.friends.0.name => "The contacts.*.name field cannot exceed 8 characters in length.",
   contacts.just.friends.2.name => "The contacts.*.name field cannot exceed 8 characters in length.",
 ]

 After: Captures no data for `contacts.*.name`.
 [
   contacts.*.name => string (38) "The contacts.*.name field is required.",
 ]
*/
