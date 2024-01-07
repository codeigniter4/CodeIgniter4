<?php

// In app/Config/Routing.php
use CodeIgniter\Config\Routing as BaseRouting;

// ...
class Routing extends BaseRouting
{
    // ...
    public bool $prioritize = true;
    // ...
}

// In app/Config/Routes.php
// to enable
$routes->setPrioritize();

// to disable
$routes->setPrioritize(false);
