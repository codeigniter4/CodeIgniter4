<?php

// All lookup attributes can be passed as an object or Entity.
$attrs        = new \stdClass();
$attrs->email = 'john@example.com';

$user = $userModel->firstOrInsert($attrs, ['name' => 'John Doe', 'country' => 'US']);
