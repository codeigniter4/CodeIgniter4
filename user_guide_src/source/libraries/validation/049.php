<?php

namespace App\Validation;

use App\ValueObject\Name;

class UserRules
{
    public function valid_name($name, ?string &$error = null)
    {
        if (! $name instanceof Name) {
            $error = 'The name should be Name::class.';

            return false;
        }

        if ($name->value === '') {
            $error = 'The name should not be empty.';

            return false;
        }

        return true;
    }
}
