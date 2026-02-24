<?php

// Get a simple page
$result = $this->call('GET', '/');

// Submit a form
$result = $this->call('POST', 'contact', [
    'name'  => 'Fred Flintstone',
    'email' => 'flintyfred@example.com',
]);
