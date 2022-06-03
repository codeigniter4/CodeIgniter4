<?php

namespace Config;

class Validation
{
    public $signup = [
        'username'     => 'required',
        'password'     => 'required',
        'pass_confirm' => 'required|matches[password]',
        'email'        => 'required|valid_email',
    ];

    public $signup_errors = [
        'username' => [
            'required' => 'You must choose a username.',
        ],
        'email' => [
            'valid_email' => 'Please check the Email field. It does not appear to be valid.',
        ],
    ];

    // ...
}
