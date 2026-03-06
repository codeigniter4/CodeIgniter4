<?php

class MyRules
{
    public function even($value, ?string &$error = null): bool
    {
        if ((int) $value % 2 !== 0) {
            $error = lang('myerrors.evenError');
            // You can also use {field}, {param}, and {value} placeholders:
            // $error = 'The value of {field} is not even.';

            return false;
        }

        return true;
    }
}
