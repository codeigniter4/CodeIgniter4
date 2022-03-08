<?php

$validation->setRules(
    [
        'username' => 'required|is_unique[users.username]',
        'password' => 'required|min_length[10]',
    ],
    [   // Errors
        'username' => [
            'required' => 'All accounts must have usernames provided',
        ],
        'password' => [
            'min_length' => 'Your password is too short. You want to get hacked?',
        ],
    ]
);
