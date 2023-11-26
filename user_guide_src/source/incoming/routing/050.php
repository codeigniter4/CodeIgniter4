<?php

// In app/Config/Routing.php
class Routing extends BaseRouting
{
    // ...
    public bool $autoRoute = false;
    // ...
}

// This can be overridden in the Routes file
$routes->setAutoRoute(false);
