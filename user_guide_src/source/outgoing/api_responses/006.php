<?php

// Example response with a single error message
$response = [
    'status'   => 400,
    'code'     => '321',
    'messages' => [
        'error' => 'An error occurred',
    ],
];

// Example response with multiple error messages per field
$response = [
    'status'   => 400,
    'code'     => '321a',
    'messages' => [
        'foo' => 'Error message 1',
        'bar' => 'Error message 2',
    ],
];
