Upgrade Routing
##################

.. contents::
    :local:
    :depth: 1


Documentations
==============

- `URI Routing Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/general/routing.html>`_
- :doc:`URI Routing Documentation CodeIgniter 4.X </incoming/routing>`


What has been changed
=====================
- In CI4 the routing is no longer configured by setting the routes as array.

Upgrade Guide
=============
1. You have to change the syntax of each routing line and append it in **app/Config/Routes.php**. For example:

    - ``$route['journals'] = 'blogs';`` to ``$routes->add('journals', 'Blogs::index');``. This would map to the ``index()`` method in the ``Blogs`` controller.
    - ``$route['product/(:any)'] = 'catalog/product_lookup';`` to ``$routes->add('product/(:any)', 'Catalog::productLookup');``
    - ``$route['login/(.+)'] = 'auth/login/$1';`` to ``$routes->add('login/(.+)', 'Auth::login/$1');``

Code Example
============

CodeIgniter Version 3.11
------------------------
Path: **application/config/routes.php**::

    <?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    $route['posts/index'] = 'posts/index';
    $route['teams/create'] = 'teams/create';
    $route['teams/update'] = 'teams/update';

    $route['posts/create'] = 'posts/create';
    $route['posts/update'] = 'posts/update';
    $route['drivers/create'] = 'drivers/create';
    $route['drivers/update'] = 'drivers/update';
    $route['posts/(:any)'] = 'posts/view/$1';

CodeIgniter Version 4.x
-----------------------
Path: **app/Config/Routes.php**::

    <?php

    namespace Config;

    // Create a new instance of our RouteCollection class.
    $routes = Services::routes();

    // Load the system's routing file first, so that the app and ENVIRONMENT
    // can override as needed.
    if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
        require SYSTEMPATH . 'Config/Routes.php';
    }

    ...

    $routes->add('posts/index', 'Posts::index');
    $routes->add('teams/create', 'Teams::create');
    $routes->add('teams/update', 'Teams::update');

    $routes->add('posts/create', 'Posts::create');
    $routes->add('posts/update', 'Posts::update');
    $routes->add('drivers/create', 'Drivers::create');
    $routes->add('drivers/update', 'Drivers::update');
    $routes->add('posts/(:any)', 'Posts::view/$1');
