<?php

// Get the router instance.
/** @var \CodeIgniter\Router\Router $router */
$router  = service('router');
$options = $router->getMatchedRouteOptions();

echo 'Route name: ' . $options['as'];

print_r($options);

// Route name: api:auth
//
// Array
// (
//     [filter] => api-auth
//     [namespace] => App\API\v1
//     [hostname] => example.com
//     [subdomain] => api
//     [offset] => 1
//     [priority] => 1
//     [as] => api:auth
// )
