<?php

// for data
'contacts' => [
    'friends' => [
        [
            'name' => 'Fred Flinstone',
        ],
        [
            'name' => '',
        ],
    ]
];

// rule
// contacts.*.name => 'required'

// error will be
//'contacts.friends.1.name' => 'The contacts.*.name field is required.',
