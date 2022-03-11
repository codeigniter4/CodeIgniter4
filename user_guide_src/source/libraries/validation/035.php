<?php

class MyRules
{
    public function even(string $str, ?string &$error = null): bool
    {
        if ((int) $str % 2 !== 0) {
            $error = lang('myerrors.evenError');

            return false;
        }

        return true;
    }
}
