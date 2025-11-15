<?php

namespace Config;

// ...

class Validation extends BaseConfig
{
    // ...

    public array $signup = [
        'username' => [
            'rules'  => 'required|max_length[30]',
            'errors' => [
                'required' => 'You must choose a Username.',
            ],
        ],
        'email' => [
            'rules'  => 'required|max_length[254]|valid_email',
            'errors' => [
                'valid_email' => 'Please check the Email field. It does not appear to be valid.',
            ],
        ],
    ];

    // ...
}
