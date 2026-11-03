<?php

namespace Config;

// ...

class Validation extends BaseConfig
{
    // ...

    public array $templates = [
        'list'    => 'CodeIgniter\Validation\Views\list',
        'single'  => 'CodeIgniter\Validation\Views\single',
        'my_list' => '_errors_list',
    ];

    // ...
}
