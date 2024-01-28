<?php

// In app/Config/Routing.php
use CodeIgniter\Config\Routing as BaseRouting;

// ...
class Routing extends BaseRouting
{
    // ...
    public bool $autoRoute = false;
    // ...
}

// This can be overridden in app/Config/Routes.php
$routes->setAutoRoute(false);
