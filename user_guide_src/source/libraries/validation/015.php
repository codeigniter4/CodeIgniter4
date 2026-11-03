<?php

namespace Config;

// ...

class Validation extends BaseConfig
{
    // ...

    public array $signup = [
        'username'     => 'required|max_length[30]',
        'password'     => 'required|max_length[255]',
        'pass_confirm' => 'required|max_length[255]|matches[password]',
        'email'        => 'required|max_length[254]|valid_email',
    ];

    public array $signup_errors = [
        'username' => [
            'required' => 'You must choose a username.',
        ],
        'email' => [
            'valid_email' => 'Please check the Email field. It does not appear to be valid.',
        ],
    ];

    // ...
}
