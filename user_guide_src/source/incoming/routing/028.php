<?php

$routes->environment('development', static function ($routes) {
    $routes->get('builder', 'Tools\Builder::index');
});
