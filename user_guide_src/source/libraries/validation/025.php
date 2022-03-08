<?php

$validation->setRules(
    [
        'username' => [
            'label'  => 'Rules.username',
            'rules'  => 'required|is_unique[users.username]',
            'errors' => [
                'required' => 'Rules.username.required',
            ],
        ],
        'password' => [
            'label'  => 'Rules.password',
            'rules'  => 'required|min_length[10]',
            'errors' => [
                'min_length' => 'Rules.password.min_length',
            ],
        ],
    ]
);
