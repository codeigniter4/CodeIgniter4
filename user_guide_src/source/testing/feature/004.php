<?php

$routes = [
    ['GET', 'users', 'UserController::list'],
];

$result = $this->withRoutes($routes)->get('users');
