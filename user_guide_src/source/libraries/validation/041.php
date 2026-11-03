<?php

$validation->setRules([
    'foo' => [
        'required',
        static function ($value, $data, &$error, $field) {
            if ((int) $value % 2 === 0) {
                return true;
            }

            $error = 'The value is not even.';

            return false;
        },
    ],
]);
