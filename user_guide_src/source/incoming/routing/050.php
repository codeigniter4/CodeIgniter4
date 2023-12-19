<?php

// In app/Config/Routing.php
class Routing extends BaseRouting
{
    // ...
    public bool $autoRoute = false;
    // ...
}

// This can be overridden in app/Config/Routes.php
$routes->setAutoRoute(false);
