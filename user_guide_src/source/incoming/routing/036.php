<?php

$routes->get('admin', ' AdminController::index', ['filter' => \App\Filters\SomeFilter::class]);
