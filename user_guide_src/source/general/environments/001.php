<?php

$environment = service('environment');

// Get the current environment name.
echo $environment->get();

// Check against the three built-in environments.
$environment->isProduction();
$environment->isDevelopment();
$environment->isTesting();

// Match any one of several environments (useful for custom names).
$environment->is('production', 'staging');
