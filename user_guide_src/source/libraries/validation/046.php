<?php

namespace App\Controllers;

class Form extends BaseController
{
    // Define a custom validation rule.
    public function _ruleEven($value): bool
    {
        return (int) $value % 2 === 0;
    }

    public function process()
    {
        // ...

        $validation = service('validation');
        $validation->setRules(
            [
                'foo' => [
                    'required',
                    // Specify the method in this controller as a rule.
                    [$this, '_ruleEven'],
                ],
            ],
            [
                // Errors
                'foo' => [
                    // Specify the array key for the callable rule.
                    1 => 'The value is not even.',
                ],
            ],
        );

        if (! $validation->run($data)) {
            // handle validation errors
        }

        // ...
    }
}
