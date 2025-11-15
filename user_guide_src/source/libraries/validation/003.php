<?php

namespace Config;

// ...

class Validation extends BaseConfig
{
    // ...

    public array $ruleSets = [
        \CodeIgniter\Validation\CreditCardRules::class,
        \CodeIgniter\Validation\FileRules::class,
        \CodeIgniter\Validation\FormatRules::class,
        \CodeIgniter\Validation\Rules::class,
    ];

    // ...
}
