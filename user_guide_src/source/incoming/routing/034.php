<?php

$routes->get('admin', ' AdminController::index', ['filter' => 'admin-auth']);
