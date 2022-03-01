<?php

namespace Config;

class Validation
{
    public $signup = [
        'username' => [
            'rules'  => 'required',
            'errors' => [
                'required' => 'You must choose a Username.',
            ],
        ],
        'email' => [
            'rules'  => 'required|valid_email',
            'errors' => [
                'valid_email' => 'Please check the Email field. It does not appear to be valid.',
            ],
        ],
    ];
    // ...
}
