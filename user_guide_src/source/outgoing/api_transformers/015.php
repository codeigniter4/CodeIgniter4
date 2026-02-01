<?php

use App\Transformers\UserTransformer;

$user        = new \stdClass();
$user->id    = 1;
$user->name  = 'John Doe';
$user->email = 'john@example.com';

$transformer = new UserTransformer();
$result      = $transformer->transform($user);
