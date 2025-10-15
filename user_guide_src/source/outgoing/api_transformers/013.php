<?php

use App\Entities\User;
use App\Transformers\UserTransformer;

$user = new User([
    'id'    => 1,
    'name'  => 'John Doe',
    'email' => 'john@example.com',
]);

$transformer = new UserTransformer();
$result      = $transformer->transform($user);
