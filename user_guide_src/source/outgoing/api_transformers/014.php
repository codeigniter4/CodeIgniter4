<?php

use App\Transformers\UserTransformer;

$userData = [
    'id'    => 1,
    'name'  => 'John Doe',
    'email' => 'john@example.com',
];

$transformer = new UserTransformer();
$result      = $transformer->transform($userData);
