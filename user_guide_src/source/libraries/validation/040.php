<?php

$validation->setRules(
    [
        'foo' => [
            'required',
            static fn ($value) => (int) $value % 2 === 0,
        ],
    ],
    [
        // Errors
        'foo' => [
            // Specify the array key for the closure rule.
            1 => 'The value is not even.',
        ],
    ],
);

if (! $validation->run($data)) {
    // handle validation errors
}
