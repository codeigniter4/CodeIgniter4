<?php

$routes->get('admin', ' AdminController::index', ['filter' => ['admin-auth', \App\Filters\SomeFilter::class]]);
