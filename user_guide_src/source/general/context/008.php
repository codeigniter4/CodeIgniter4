<?php

$userData = $context->getOnly(['user_id', 'username']);
// Returns: ['user_id' => 123, 'username' => 'john_doe']

// You can also pass a single key as a string
$userId = $context->getOnly('user_id');
// Returns: ['user_id' => 123]
