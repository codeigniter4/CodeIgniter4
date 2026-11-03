<?php

use App\Transformers\UserTransformer;

$users = model('UserModel')->findAll();

$transformer = new UserTransformer();
$results     = $transformer->transformMany($users);

// $results is an array of transformed user arrays
foreach ($results as $user) {
    // Each $user is the result of calling transform() on an individual user
}
