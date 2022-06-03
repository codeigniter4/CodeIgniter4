<?php

$validationRules = [
    'username' => 'required|alpha_numeric_space|min_length[3]',
    'email'    => [
        'rules'  => 'required|valid_email|is_unique[users.email]',
        'errors' => [
            'required' => 'We really need your email.',
        ],
    ],
];
$model->setValidationRules($validationRules);
