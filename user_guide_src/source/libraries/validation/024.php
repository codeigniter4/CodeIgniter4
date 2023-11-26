<?php

$validation->setRules([
    'username' => [
        'label'  => 'Username',
        'rules'  => 'required|max_length[30]|is_unique[users.username]',
        'errors' => [
            'required' => 'All accounts must have {field} provided',
        ],
    ],
    'password' => [
        'label'  => 'Password',
        'rules'  => 'required|max_length[255]|min_length[10]',
        'errors' => [
            'min_length' => 'Your {field} is too short. You want to get hacked?',
        ],
    ],
]);
