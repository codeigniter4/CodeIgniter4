<?php

// Get the router instance.
/** @var \CodeIgniter\Router\Router $router */
$router = service('router');

// Retrieve the fully qualified class name of the controller handling the current request.
$controller = $router->controllerName();

// Retrieve the method name being executed in the controller for the current request.
$method = $router->methodName();

echo 'Current Controller: ' . $controller . '<br>';
echo 'Current Method: ' . $method;
