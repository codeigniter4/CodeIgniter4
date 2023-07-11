<?php

$validation->setRules(
    [
        'username' => 'required|max_length[30]|is_unique[users.username]',
        'password' => 'required|max_length[254]|min_length[10]',
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
