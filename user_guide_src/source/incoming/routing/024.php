<?php

$routes->environment('development', function ($routes) {
    $routes->get('builder', 'Tools\Builder::index');
});
