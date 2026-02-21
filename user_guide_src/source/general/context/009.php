<?php

$data = $context->getExcept(['password', 'api_key']);
// Returns all data except 'password' and 'api_key'

// You can also pass a single key as a string
$data = $context->getExcept('password');
// Returns all data except 'password'
