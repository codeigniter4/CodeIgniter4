<?php

// In app/Config/Routing.php
use CodeIgniter\Config\Routing as BaseRouting;

// ...
class Routing extends BaseRouting
{
    // ...
    public ?string $override404 = 'App\Errors::show404';
    // ...
}

// In app/Config/Routes.php
// Would execute the show404 method of the App\Errors class
$routes->set404Override('App\Errors::show404');

// Will display a custom view
$routes->set404Override(static function () {
    echo view('my_errors/not_found.html');
});
