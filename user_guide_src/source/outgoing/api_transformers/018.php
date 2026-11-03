<?php

use App\Transformers\UserTransformer;

$user = model('UserModel')->find(1);

$transformer = new UserTransformer();

// Transform an entity
$result = $transformer->transform($user);

// Transform an array
$userData = ['id' => 1, 'name' => 'John Doe'];
$result   = $transformer->transform($userData);

// Use toArray() data
$result = $transformer->transform();
