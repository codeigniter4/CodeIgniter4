<?php

// Get a simple page
$result = $this->call('get', '/');

// Submit a form
$result = $this->call('post', 'contact', [
    'name'  => 'Fred Flintstone',
    'email' => 'flintyfred@example.com',
]);
