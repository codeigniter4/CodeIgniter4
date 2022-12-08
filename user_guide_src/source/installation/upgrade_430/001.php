<?php

/*
 * When you have the route:
 * $routes->get('(:any)', 'Pages::index/$1');
 */

namespace App\Controllers;

class Pages extends BaseController
{
    public function index($page = 'home')
    {
        /*
         * When navigating to `http://example.com/abc/def`,
         * $page will be `abc/def`, not `abc`.
         */

        // ...
    }
}
