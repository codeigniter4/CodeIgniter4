<?php

namespace App\Controllers;

use Config\Services;

class Form extends BaseController
{
    // Define a custom validation rule.
    public function _ruleEven($value, $data, &$error, $field): bool
    {
        if ((int) $value % 2 === 0) {
            return true;
        }

        $error = 'The value is not even.';

        return false;
    }

    // ...
}
