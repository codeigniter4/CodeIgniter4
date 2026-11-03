<?php

use App\Controllers\Home;

$routes->get('/', [Home::class, 'index']);
