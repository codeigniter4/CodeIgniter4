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

    // ...
}
