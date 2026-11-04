# Upgrade Routing

## Documentation

- [URI Routing Documentation CodeIgniter 3.x](http://codeigniter.com/userguide3/general/routing.html)
- [URI Routing Documentation CodeIgniter 4.x](../incoming/routing.md)

## What has been changed

- In CI4 the Auto Routing is disabled by default.
- In CI4 the new more secure [Auto Routing (Improved)](../incoming/auto_routing_improved.md#auto-routing-improved) is introduced.
- In CI4 the routing is no longer configured by setting the routes as an array.
- The Wildcard `(:any)` in CI3 will be the Placeholder `(:segment)` in CI4. The `(:any)` in CI4 matches multiple segments. See [URI Routing](../incoming/routing.md#the-behavior-of-any).

## Upgrade Guide

1.  If you use the Auto Routing in the same way as CI3, you need to enable [Auto Routing (Legacy)](../incoming/routing.md#auto-routing-legacy).

2.  You have to change the syntax of each routing line and append it in **app/Config/Routes.php**. For example:

    - `$route['journals'] = 'blogs';` to `$routes->add('journals', 'Blogs::index');`. This would map to the `index()` method in the `Blogs` controller.
    - `$route['product/(:any)'] = 'catalog/product_lookup';` to `$routes->add('product/(:segment)', 'Catalog::productLookup');`. Don't forget to replace `(:any)` with `(:segment)`.
    - `$route['login/(.+)'] = 'auth/login/$1';` to `$routes->add('login/(.+)', 'Auth::login/$1');`

    !!! note "Note"
        For backward compatibility, `$routes->add()` is used here. But we strongly recommend using [HTTP verb Routes](../incoming/routing.md#http-verb-routes) like `$routes->get()` instead of `$routes->add()` for security.

## Code Example

### CodeIgniter Version 3.x

Path: **application/config/routes.php**:

<!-- lint-ignore -->
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// ...

$route['posts/index']  = 'posts/index';
$route['teams/create'] = 'teams/create';
$route['teams/update'] = 'teams/update';

$route['posts/create']   = 'posts/create';
$route['posts/update']   = 'posts/update';
$route['drivers/create'] = 'drivers/create';
$route['drivers/update'] = 'drivers/update';
$route['posts/(:any)']   = 'posts/view/$1';
```

### CodeIgniter Version 4.x

Path: **app/Config/Routes.php**:

```php
<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->add('posts/index', 'Posts::index');
$routes->add('teams/create', 'Teams::create');
$routes->add('teams/update', 'Teams::update');

$routes->add('posts/create', 'Posts::create');
$routes->add('posts/update', 'Posts::update');
$routes->add('drivers/create', 'Drivers::create');
$routes->add('drivers/update', 'Drivers::update');
$routes->add('posts/(:segment)', 'Posts::view/$1');
```

!!! note "Note"
    For backward compatibility, `$routes->add()` is used here. But we strongly recommend using [HTTP verb Routes](../incoming/routing.md#http-verb-routes) like `$routes->get()` instead of `$routes->add()` for security.
