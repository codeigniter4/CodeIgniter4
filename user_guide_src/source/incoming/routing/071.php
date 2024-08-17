<?php

use Config\Services;

// Get the router instance
$router = Services::router();

// Retrieve the fully qualified class name of the controller handling the current request.
$controller = $router->controllerName();

// Retrieve the method name being executed in the controller for the current request.
$method = $router->methodName();

echo "Current Controller: " . $controller . "<br>";
echo "Current Method: " . $method;
