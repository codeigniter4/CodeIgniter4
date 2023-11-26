<?php

$validation->setRules([
    'username' => [
        'label'  => 'Rules.username',
        'rules'  => 'required|max_length[30]|is_unique[users.username]',
        'errors' => [
            'required' => 'Rules.username.required',
        ],
    ],
    'password' => [
        'label'  => 'Rules.password',
        'rules'  => 'required|max_length[255]|min_length[10]',
        'errors' => [
            'min_length' => 'Rules.password.min_length',
        ],
    ],
]);
