###########
URI Routing
###########

.. contents::
    :local:
    :depth: 2

Typically there is a one-to-one relationship between a URL string and its corresponding
controller class/method. The segments in a URI normally follow this pattern::

    example.com/class/function/id/

In some instances, however, you may want to remap this relationship so that a different
class/method can be called instead of the one corresponding to the URL.

For example, let’s say you want your URLs to have this prototype::

    example.com/product/1/
    example.com/product/2/
    example.com/product/3/
    example.com/product/4/

Normally the second segment of the URL is reserved for the method name, but in the example
above it instead has a product ID. To overcome this, CodeIgniter allows you to remap the URI handler.

Setting your own routing rules
==============================

Routing rules are defined in the **app/config/Routes.php** file. In it you'll see that
it creates an instance of the RouteCollection class that permits you to specify your own routing criteria.
Routes can be specified using placeholders or Regular Expressions.

A route simply takes the URI on the left, and maps it to the controller and method on the right,
along with any parameters that should be passed to the controller. The controller and method should
be listed in the same way that you would use a static method, by separating the fully-namespaced class
and its method with a double-colon, like ``Users::list``. If that method requires parameters to be
passed to it, then they would be listed after the method name, separated by forward-slashes::

	// Calls the $Users->list()
	Users::list
	// Calls $Users->list(1, 23)
	Users::list/1/23

Placeholders
============

A typical route might look something like this::

    $routes->add('product/(:num)', 'App\Catalog::productLookup');

In a route, the first parameter contains the URI to be matched, while the second parameter
contains the destination it should be re-routed to. In the above example, if the literal word
"product" is found in the first segment of the URL, and a number is found in the second segment,
the "App\Catalog" class and the "productLookup" method are used instead.

Placeholders are simply strings that represent a Regular Expression pattern. During the routing
process, these placeholders are replaced with the value of the Regular Expression. They are primarily
used for readability.

The following placeholders are available for you to use in your routes:

* **(:any)** will match all characters from that point to the end of the URI. This may include multiple URI segments.
* **(:segment)** will match any character except for a forward slash (/) restricting the result to a single segment.
* **(:num)** will match any integer.
* **(:alpha)** will match any string of alphabetic characters
* **(:alphanum)** will match any string of alphabetic characters or integers, or any combination of the two.
* **(:hash)** is the same as **:segment**, but can be used to easily see which routes use hashed ids (see the :doc:`Model </models/model>` docs).

.. note:: **{locale}** cannot be used as a placeholder or other part of the route, as it is reserved for use
    in :doc:`localization </outgoing/localization>`.

Examples
========

Here are a few basic routing examples::

	$routes->add('journals', 'App\Blogs');

A URL containing the word "journals" in the first segment will be remapped to the "App\Blogs" class,
and the default method, which is usually ``index()``::

	$routes->add('blog/joe', 'Blogs::users/34');

A URL containing the segments "blog/joe" will be remapped to the “\Blogs” class and the “users” method.
The ID will be set to “34”::

	$routes->add('product/(:any)', 'Catalog::productLookup');

A URL with “product” as the first segment, and anything in the second will be remapped to the “\Catalog” class
and the “productLookup” method::

	$routes->add('product/(:num)', 'Catalog::productLookupByID/$1';

A URL with “product” as the first segment, and a number in the second will be remapped to the “\Catalog” class
and the “productLookupByID” method passing in the match as a variable to the method.

.. important:: While the ``add()`` method is convenient, it is recommended to always use the HTTP-verb-based
    routes, described below, as it is more secure. It will also provide a slight performance increase, since
    only routes that match the current request method are stored, resulting in fewer routes to scan through
    when trying to find a match.

Custom Placeholders
===================

You can create your own placeholders that can be used in your routes file to fully customize the experience
and readability.

You add new placeholders with the ``addPlaceholder`` method. The first parameter is the string to be used as
the placeholder. The second parameter is the Regular Expression pattern it should be replaced with.
This must be called before you add the route::

	$routes->addPlaceholder('uuid', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
	$routes->add('users/(:uuid)', 'Users::show/$1');

Regular Expressions
===================

If you prefer you can use regular expressions to define your routing rules. Any valid regular expression
is allowed, as are back-references.

.. important:: Note: If you use back-references you must use the dollar syntax rather than the double backslash syntax.
    A typical RegEx route might look something like this::

	$routes->add('products/([a-z]+)/(\d+)', 'Products::show/$1/id_$2');

In the above example, a URI similar to products/shirts/123 would instead call the ``show`` method
of the ``Products`` controller class, with the original first and second segment passed as arguments to it.

With regular expressions, you can also catch a segment containing a forward slash (‘/’), which would usually
represent the delimiter between multiple segments.

For example, if a user accesses a password protected area of your web application and you wish to be able to
redirect them back to the same page after they log in, you may find this example useful::

	$routes->add('login/(.+)', 'Auth::login/$1');

For those of you who don’t know regular expressions and want to learn more about them,
`regular-expressions.info <http://www.regular-expressions.info/>`_ might be a good starting point.

.. important:: Note: You can also mix and match wildcards with regular expressions.

Closures
========

You can use an anonymous function, or Closure, as the destination that a route maps to. This function will be
executed when the user visits that URI. This is handy for quickly executing small tasks, or even just showing
a simple view::

    $routes->add('feed', function()
    {
        $rss = new RSSFeeder();
        return $rss->feed('general');
    });

Mapping multiple routes
=======================

While the add() method is simple to use, it is often handier to work with multiple routes at once, using
the ``map()`` method. Instead of calling the ``add()`` method for each route that you need to add, you can
define an array of routes and then pass it as the first parameter to the `map()` method::

	$routes = [];
	$routes['product/(:num)']      = 'Catalog::productLookupById';
	$routes['product/(:alphanum)'] = 'Catalog::productLookupByName';

	$collection->map($routes);

Redirecting Routes
==================

Any site that lives long enough is bound to have pages that move. You can specify routes that should redirect
to other routes with the ``addRedirect()`` method. The first parameter is the URI pattern for the old route. The
second parameter is either the new URI to redirect to, or the name of a named route. The third parameter is
the HTTP status code that should be sent along with the redirect. The default value is ``302`` which is a temporary
redirect and is recommended in most cases::

    $routes->add('users/profile', 'Users::profile', ['as' => 'profile']);

    // Redirect to a named route
    $routes->addRedirect('users/about', 'profile');
    // Redirect to a URI
    $routes->addRedirect('users/about', 'users/profile');

If a redirect route is matched during a page load, the user will be immediately redirected to the new page before a
controller can be loaded.

Grouping Routes
===============

You can group your routes under a common name with the ``group()`` method. The group name becomes a segment that
appears prior to the routes defined inside of the group. This allows you to reduce the typing needed to build out an
extensive set of routes that all share the opening string, like when building an admin area::

	$routes->group('admin', function($routes)
	{
		$routes->add('users', 'Admin\Users::index');
		$routes->add('blog', 'Admin\Blog::index');
	});

This would prefix the 'users' and 'blog" URIs with "admin", handling URLs like ``/admin/users`` and ``/admin/blog``.
It is possible to nest groups within groups for finer organization if you need it::

	$routes->group('admin', function($routes)
	{
		$routes->group('users', function($routes)
		{
			$routes->add('list', 'Admin\Users::list');
		});

	});

This would handle the URL at ``admin/users/list``.

If you need to assign options to a group, like a `namespace <#assigning-namespace>`_, do it before the callback::

	$routes->group('api', ['namespace' => 'App\API\v1'], function($routes)
	{
		$routes->resource('users');
	});

This would handle a resource route to the ``App\API\v1\Users`` controller with the ``/api/users`` URI.

You can also use a specific `filter <filters.html>`_ for a group of routes. This will always
run the filter before or after the controller. This is especially handy during authentication or api logging::

    $routes->group('api', ['filter' => 'api-auth'], function($routes)
    {
        $routes->resource('users');
    });

The value for the filter must match one of the aliases defined within ``app/Config/Filters.php``.

Environment Restrictions
========================

You can create a set of routes that will only be viewable in a certain environment. This allows you to create
tools that only the developer can use on their local machines that are not reachable on testing or production servers.
This can be done with the ``environment()`` method. The first parameter is the name of the environment. Any
routes defined within this closure are only accessible from the given environment::

	$routes->environment('development', function($routes)
	{
		$routes->add('builder', 'Tools\Builder::index');
	});

Reverse Routing
===============

Reverse routing allows you to define the controller and method, as well as any parameters, that a link should go
to, and have the router lookup the current route to it. This allows route definitions to change without you having
to update your application code. This is typically used within views to create links.

For example, if you have a route to a photo gallery that you want to link to, you can use the ``route_to()`` helper
function to get the current route that should be used. The first parameter is the fully qualified Controller and method,
separated by a double colon (::), much like you would use when writing the initial route itself. Any parameters that
should be passed to the route are passed in next::

	// The route is defined as:
	$routes->add('users/(:id)/gallery(:any)', 'App\Controllers\Galleries::showUserGallery/$1/$2');

	// Generate the relative URL to link to user ID 15, gallery 12
	// Generates: /users/15/gallery/12
	<a href="<?= route_to('App\Controllers\Galleries::showUserGallery', 15, 12) ?>">View Gallery</a>

Using Named Routes
==================

You can name routes to make your application less fragile. This applies a name to a route that can be called
later, and even if the route definition changes, all of the links in your application built with ``route_to``
will still work without you having to make any changes. A route is named by passing in the ``as`` option
with the name of the route::

    // The route is defined as:
    $routes->add('users/(:id)/gallery(:any)', 'Galleries::showUserGallery/$1/$2', ['as' => 'user_gallery');

    // Generate the relative URL to link to user ID 15, gallery 12
    // Generates: /users/15/gallery/12
    <a href="<?= route_to('user_gallery', 15, 12) ?>">View Gallery</a>

This has the added benefit of making the views more readable, too.

Using HTTP verbs in routes
==========================

It is possible to use HTTP verbs (request method) to define your routing rules. This is particularly
useful when building RESTFUL applications. You can use any standard HTTP verb (GET, POST, PUT, DELETE, etc).
Each verb has its own method you can use::

	$routes->get('products', 'Product::feature');
	$routes->post('products', 'Product::feature');
	$routes->put('products/(:num)', 'Product::feature');
	$routes->delete('products/(:num)', 'Product::feature');

You can supply multiple verbs that a route should match by passing them in as an array to the ``match`` method::

	$routes->match(['get', 'put'], 'products', 'Product::feature');

Command-Line only Routes
========================

You can create routes that work only from the command-line, and are inaccessible from the web browser, with the
``cli()`` method. This is great for building cronjobs or CLI-only tools. Any route created by any of the HTTP-verb-based
route methods will also be inaccessible from the CLI, but routes created by the ``any()`` method will still be
available from the command line::

	$routes->cli('migrate', 'App\Database::migrate');

Global Options
==============

All of the methods for creating a route (add, get, post, `resource <restful.html>`_ etc) can take an array of options that
can modify the generated routes, or further restrict them. The ``$options`` array is always the last parameter::

	$routes->add('from', 'to', $options);
	$routes->get('from', 'to', $options);
	$routes->post('from', 'to', $options);
	$routes->put('from', 'to', $options);
	$routes->head('from', 'to', $options);
	$routes->options('from', 'to', $options);
	$routes->delete('from', 'to', $options);
	$routes->patch('from', 'to', $options);
	$routes->match(['get', 'put'], 'from', 'to', $options);
	$routes->resource('photos', $options);
	$routes->map($array, $options);
	$routes->group('name', $options, function());

Applying Filters
----------------

You can alter the behavior of specific routes by supplying a filter to run before or after the controller. This is especially handy during authentication or api logging::

    $routes->add('admin',' AdminController::index', ['filter' => 'admin-auth']);

The value for the filter must match one of the aliases defined within ``app/Config/Filters.php``. You may also supply parameters to be passed to the filter's ``before()`` and ``after()`` methods::

    $routes->add('users/delete/(:segment)', 'AdminController::index', ['filter' => 'admin-auth:dual,noreturn']);

See `Controller filters <filters.html>`_ for more information on setting up filters.

Assigning Namespace
-------------------

While a default namespace will be prepended to the generated controllers (see below), you can also specify
a different namespace to be used in any options array, with the ``namespace`` option. The value should be the
namespace you want modified::

	// Routes to \Admin\Users::index()
	$routes->add('admin/users', 'Users::index', ['namespace' => 'Admin']);

The new namespace is only applied during that call for any methods that create a single route, like get, post, etc.
For any methods that create multiple routes, the new namespace is attached to all routes generated by that function
or, in the case of ``group()``, all routes generated while in the closure.

Limit to Hostname
-----------------

You can restrict groups of routes to function only in certain domain or sub-domains of your application
by passing the "hostname" option along with the desired domain to allow it on as part of the options array::

	$collection->get('from', 'to', ['hostname' => 'accounts.example.com']);

This example would only allow the specified hosts to work if the domain exactly matched "accounts.example.com".
It would not work under the main site at "example.com".

Limit to Subdomains
-------------------

When the ``subdomain`` option is present, the system will restrict the routes to only be available on that
sub-domain. The route will only be matched if the subdomain is the one the application is being viewed through::

	// Limit to media.example.com
	$routes->add('from', 'to', ['subdomain' => 'media']);

You can restrict it to any subdomain by setting the value to an asterisk, (*). If you are viewing from a URL
that does not have any subdomain present, this will not be matched::

	// Limit to any sub-domain
	$routes->add('from', 'to', ['subdomain' => '*']);

.. important:: The system is not perfect and should be tested for your specific domain before being used in production.
	Most domains should work fine but some edge case ones, especially with a period in the domain itself (not used
	to separate suffixes or www) can potentially lead to false positives.

Offsetting the Matched Parameters
---------------------------------

You can offset the matched parameters in your route by any numeric value with the ``offset`` option, with the
value being the number of segments to offset.

This can be beneficial when developing API's with the first URI segment being the version number. It can also
be used when the first parameter is a language string::

	$routes->get('users/(:num)', 'users/show/$1', ['offset' => 1]);

	// Creates:
	$routes['users/(:num)'] = 'users/show/$2';

Routes Configuration Options
============================

The RoutesCollection class provides several options that affect all routes, and can be modified to meet your
application's needs. These options are available at the top of `/app/Config/Routes.php`.

Default Namespace
-----------------

When matching a controller to a route, the router will add the default namespace value to the front of the controller
specified by the route. By default, this value is empty, which leaves each route to specify the fully namespaced
controller::

    $routes->setDefaultNamespace('');

    // Controller is \Users
    $routes->add('users', 'Users::index');

    // Controller is \Admin\Users
    $routes->add('users', 'Admin\Users::index');

If your controllers are not explicitly namespaced, there is no need to change this. If you namespace your controllers,
then you can change this value to save typing::

	$routes->setDefaultNamespace('App');

	// Controller is \App\Users
	$routes->add('users', 'Users::index');

	// Controller is \App\Admin\Users
	$routes->add('users', 'Admin\Users::index');

Default Controller
------------------

When a user visits the root of your site (i.e. example.com) the controller to use is determined by the value set by
the ``setDefaultController()`` method, unless a route exists for it explicitly. The default value for this is ``Home``
which matches the controller at ``/app/Controllers/Home.php``::

	// example.com routes to app/Controllers/Welcome.php
	$routes->setDefaultController('Welcome');

The default controller is also used when no matching route has been found, and the URI would point to a directory
in the controllers directory. For example, if the user visits ``example.com/admin``, if a controller was found at
``/app/Controllers/admin/Home.php`` it would be used.

Default Method
--------------

This works similar to the default controller setting, but is used to determine the default method that is used
when a controller is found that matches the URI, but no segment exists for the method. The default value is
``index``::

	$routes->setDefaultMethod('listAll');

In this example, if the user were to visit example.com/products, and a Products controller existed, the
``Products::listAll()`` method would be executed.

Translate URI Dashes
--------------------

This option enables you to automatically replace dashes (‘-‘) with underscores in the controller and method
URI segments, thus saving you additional route entries if you need to do that. This is required because the
dash isn’t a valid class or method name character and would cause a fatal error if you try to use it::

	$routes->setTranslateURIDashes(true);

Use Defined Routes Only
-----------------------

When no defined route is found that matches the URI, the system will attempt to match that URI against the
controllers and methods as described above. You can disable this automatic matching, and restrict routes
to only those defined by you, by setting the ``setAutoRoute()`` option to false::

	$routes->setAutoRoute(false);

404 Override
------------

When a page is not found that matches the current URI, the system will show a generic 404 view. You can change
what happens by specifying an action to happen with the ``set404Override()`` option. The value can be either
a valid class/method pair, just like you would show in any route, or a Closure::

    // Would execute the show404 method of the App\Errors class
    $routes->set404Override('App\Errors::show404');

    // Will display a custom view
    $routes->set404Override(function()
    {
        echo view('my_errors/not_found.html');
    });
