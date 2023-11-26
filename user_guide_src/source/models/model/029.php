<?php

$validationRules = [
    'username' => 'required|max_length[30]|alpha_numeric_space|min_length[3]',
    'email'    => [
        'rules'  => 'required|max_length[254]|valid_email|is_unique[users.email]',
        'errors' => [
            'required' => 'We really need your email.',
        ],
    ],
];
$model->setValidationRules($validationRules);
