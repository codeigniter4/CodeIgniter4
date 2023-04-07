<?php

// In app/Config/Routing.php
class Routing extends BaseRouting
{
    // ...
    public $override404 = 'App\Errors::show404';
    // ...
}

// Would execute the show404 method of the App\Errors class
$routes->set404Override('App\Errors::show404');

// Will display a custom view
$routes->set404Override(static function () {
    echo view('my_errors/not_found.html');
});
