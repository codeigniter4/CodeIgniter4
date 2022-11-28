<?php

namespace Config;

class Validation
{
    public $ruleSets = [
        \CodeIgniter\Validation\CreditCardRules::class,
        \CodeIgniter\Validation\FileRules::class,
        \CodeIgniter\Validation\FormatRules::class,
        \CodeIgniter\Validation\Rules::class,
    ];

    // ...
}
