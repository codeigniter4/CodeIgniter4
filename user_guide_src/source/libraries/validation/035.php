<?php

class MyRules
{
    public function even($value, ?string &$error = null): bool
    {
        if ((int) $value % 2 !== 0) {
            $error = lang('myerrors.evenError');

            return false;
        }

        return true;
    }
}
