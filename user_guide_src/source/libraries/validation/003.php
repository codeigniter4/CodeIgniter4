<?php

namespace Config;

class Validation
{
    public $ruleSets = [
        \CodeIgniter\Validation\StrictRules\CreditCardRules::class,
        \CodeIgniter\Validation\StrictRules\FileRules::class,
        \CodeIgniter\Validation\StrictRules\FormatRules::class,
        \CodeIgniter\Validation\StrictRules\Rules::class,
    ];

    // ...
}
