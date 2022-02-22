<?php

$routes = [
    ['get', 'users', 'UserController::list'],
];

$result = $this->withRoutes($routes)->get('users');
