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

    // ...
}
