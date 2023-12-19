<?php

// In app/Config/Routing.php
class Routing extends BaseRouting
{
    // ...
    public bool $translateURIDashes = true;
    // ...
}

// This can be overridden in app/Config/Routes.php
$routes->setTranslateURIDashes(true);
