###########
URI Routing
###########

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

Routing rules are defined in the ``application/config/routes.php`` file. In it you'll see that
it creates an instance of the RouteCollection class that permits you to specify your own routing criteria.
Routes can be specified using placeholders or Regular Expressions.

A route simply takes the URI on the left, and maps it to the controller and method on the right,
along with any parameters that should be passed to the controller. The controller and method should
be listed in the same way that you would use a static method, by separating the fully-namespaced class
and its method with a double-colon, like ``Users::list``.  If that method requires parameters to be
passed to it, then they would be listed after the method name, separated by forward-slashes::

	// Calls the $Users->list()
	Users::list
	// Calls $Users->list(1, 23)
	Users::list/1/23

Placeholders
============

A typical route might look something like this::

    $collection->add('product/:num', 'App\Catalog::productLookup');
   
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
* **(alphanum)** will match any string of alphabetic characters or integers, or any combination of the two.

Examples
========

Here are a few basic routing examples::

	$collection->add('journals', 'App\Blogs');

A URL containing the word "journals" in the first segment will be remapped to the "App\Blogs" class,
and the default method, which is usually ``index()``::

	$collection->add('blog/joe', 'Blogs::users/34');

A URL containing the segments "blog/joe" will be remapped to the “\Blogs” class and the “users” method.
The ID will be set to “34”::

	$collection->add('product/(:any)', 'Catalog/productLookup');
	
A URL with “product” as the first segment, and anything in the second will be remapped to the “\Catalog” class
and the “productLookup” method::

	$collection->add('product/(:num)', 'Catalog/productLookupByID/$1';
	
A URL with “product” as the first segment, and a number in the second will be remapped to the “\Catalog” class
and the “productLookupByID” method passing in the match as a variable to the method.


Custom Placeholders
===================

You can create your own placeholders that can be used in your routes file to fully customize the experience
and readability.

You add new placeholders with the ``addPlaceholder`` method. The first parameter is the string to be used as
the placeholder. The second parameter is the Regular Expression pattern it should be replaced with.
This must be called before you add the route::

	$collection->addPlaceholder('uuid', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
	$collection->add('users/(:uuid)', 'Users::show/$1');


Regular Expressions
===================

If you prefer you can use regular expressions to define your routing rules. Any valid regular expression
is allowed, as are back-references.

.. important::Note: If you use back-references you must use the dollar syntax rather than the double backslash syntax.
A typical RegEx route might look something like this::

	$collection->add('products/([a-z]+)/(\d+)', '$1/id_$2');

In the above example, a URI similar to products/shirts/123 would instead call the “\Shirts” controller class
and the “id_123” method.

With regular expressions, you can also catch a segment containing a forward slash (‘/’), which would usually
represent the delimiter between multiple segments.

For example, if a user accesses a password protected area of your web application and you wish to be able to
redirect them back to the same page after they log in, you may find this example useful::

	$collection->add('login/(.+)', 'Auth::login/$1');
	
For those of you who don’t know regular expressions and want to learn more about them,
`regular-expressions.info <http://www.regular-expressions.info/>`_ might be a good starting point.

.. important:: Note: You can also mix and match wildcards with regular expressions.


Using HTTP verbs in routes
==========================

It is possible to use HTTP verbs (request method) to define your routing rules. This is particularly
useful when building RESTFUL applications. You can use any standard HTTP verbs (GET, POST, PUT, DELETE, etc).
HTTP verb rules are case-insensitive. All you need to do is supply the verb as the third parameter in the add()
method::

	$collection->add('products', 'Product::feature', 'put');

In this example, a PUT request to the URI "products" would call the ``Product::feature()`` controller method.

You can supply multiple verbs that a route should match by passing them in as an array::

	$collection->add('products', 'Product::feature', ['get', 'put']);

If no HTTP verb is specified, it will match any request method.

Closures
========

You can use an anonymous function, or Closure, as the destination that a route maps to. This function will be
executed when the user visits that URI. This is handy for quickly executing small tasks, or even just showing
a simple view::

	$collection->add('feed', function() 
		{
			$rss = new RSSFeeder();
			return $rss->feed('general');
		{
	);

Mapping multiple routes
=======================

While the add() method is simple to use, it is often handier to work with multiple routes at once, using
the ``map()`` method. This also provides additional options that are not available in the ``add()`` method
and is the preferred way to add routes.

Instead of calling the ``add()`` method for each route that you need to add, you can define an array of
routes and then pass it as the first parameter to the `map()` method::

	$routes = [];
	$routes['product/(:num)'] = 'Catalog/productLookupById';
	$routes['product/(:alphanum)'] = 'Catalog/productLookupByName';
	
	$collection->map($routes);
	
The second parameter of the map method takes an array of options that will modify or restrict the routes
in one way or another. The options are discussed below. Any options passed to the map() method only affect
the routes passed in along with those options. This allows you to segment your routes into multiple chunks
that are each processed slightly differently::

	$routes = [];
	$routes['products'] = 'Products';
	$routes['products/(:num)'] = 'Products::edit/$1';
	
	$collection->map($routes, ['prefix' => 'admin');
	
	$routes = [];
	$routes['products'] = 'Products';
	$routes['products/(:num)'] = 'Products::edit/$1';
	
	$collection->map($routes, ['prefix' => 'manage');

	
HTTP Verbs
==========

You can still use HTTP verbs in your routing when you do it this way, though the syntax is necessarily different::

	$routes['product']['put'] = 'Product::insert';
	$routes['product']['delete'] = 'Product::delete/$1';
	
	$collection->map($routes);

Prefixing Routes
================

You can prefix your routes with a common string by passing an array with the key of 'prefix' and it's value in
as the second parameter to the map() method. This allows you to reduce the typing needed to build out an
extensive set of routes that all share the opening string, like when building an admin area::

	$routes['products'] = 'Admin\Products';
	$routes['products/(:num)'] = 'Admin\Products::edit/$1';
	
	$collection->map($routes, ['prefix' => 'admin']);
	
This would prefix both of the "products" URIs with "admin", handling URLs like "/admin/products" and "/admin/products/34". 

Modify Namespace
================

You can assign the same namespace used to a group of controllers by passing the "namespace" option in to
the map() method::

	$routes['products'] = 'Products';
	$routes['users'] = 'Users';
	
	$collection->map($routes, ['namespace' => '\App\Admin']);

This example applies the '\App\Admin' namespace to the "users" and "products" controllers. This would cause
the router to look for '\App\Admin\Products' instead of the default '\Products' controller.

Hostname Restriction
====================

You can restrict groups of routes to function only in certain domain or sub-domains of your application
by passing the "hostname" option along with the desired domain to allow it on::

	$collection->map($routes, ['hostname' => 'accounts.example.com']);

This example would only allow the specified hosts to work if the domain exactly matched "accounts.example.com".
 It would not work under the main site at "example.com".


Reverse Routing
===============

Reverse routing allows you to define the controller and method, as well as any parameters, that a link should go
to, and have the router lookup the current route to it. This allows route definitions to change without you having
to update your application code. This is typically used within views to create links.

For example, if you have a route to a photo gallery that you want to link to, you can use the ``route_to()`` helper
function to get the current route that should be used. The first parameter is the Controller and method, written
just as it would be defined the destination of a route. Any parameters that should be passed to the route are
passed in next::

	// The route is defined as:
	$routes->add('users/(:id)/gallery(:any)', 'Galleries::showUserGallery/$1/$2');

	// Generate the relative URL to link to user ID 15, gallery 12
	// Generates: /users/15/gallery/12
	<a href="<?= route_to('Galleries::showUserGallery', 15, 12) ?>">View Gallery</a>
