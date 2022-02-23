###########
URI Routing
###########

.. contents::
    :local:
    :depth: 2

******************************
Auto Routes and Defined Routes
******************************

Auto Routes
===========

Typically there is a one-to-one relationship between a URL string and its corresponding
controller class/method. The segments in a URI normally follow this pattern::

    example.com/class/method/id/

We call this "**Auto Routes**". CodeIgniter automatically routes an HTTP request,
and executes the corresponding controller method. The auto-routing is enabled by default.

.. note:: To prevent misconfiguration and miscoding, we recommend that you disable
    the auto-routing feature. See :ref:`use-defined-routes-only`.

.. important:: The auto-routing routes a HTTP request with **any** HTTP method to a controller method.

Defined Routes
==============

In some instances, however, you may want to remap this relationship so that a different
class/method can be called instead of the one corresponding to the URL.

For example, let’s say you want your URLs to have this prototype::

    example.com/product/1/
    example.com/product/2/
    example.com/product/3/
    example.com/product/4/

Normally the second segment of the URL path is reserved for the method name, but in the example
above it instead has a product ID. To overcome this, CodeIgniter allows you to remap the URI handler.
We call this "**Defined Routes**".

******************************
Setting Your Own Routing Rules
******************************

Routing rules are defined in the **app/Config/Routes.php** file. In it you'll see that
it creates an instance of the RouteCollection class (``$routes``) that permits you to specify your own routing criteria.
Routes can be specified using placeholders or Regular Expressions.

When you specify a route, you choose a method to corresponding to HTTP verbs (request method).
If you expect a GET request, you use the ``get()`` method:

.. literalinclude:: routing/001.php
   :lines: 2-

A route simply takes the URI path on the left, and maps it to the controller and method on the right,
along with any parameters that should be passed to the controller. The controller and method should
be listed in the same way that you would use a static method, by separating the class
and its method with a double-colon, like ``Users::list``. If that method requires parameters to be
passed to it, then they would be listed after the method name, separated by forward-slashes:

.. literalinclude:: routing/002.php
   :lines: 2-

You can use any standard HTTP verb (GET, POST, PUT, DELETE, etc):

.. literalinclude:: routing/003.php
   :lines: 2-

You can supply multiple verbs that a route should match by passing them in as an array to the ``match()`` method:

.. literalinclude:: routing/004.php
   :lines: 2-

Placeholders
============

A typical route might look something like this:

.. literalinclude:: routing/005.php
   :lines: 2-

In a route, the first parameter contains the URI to be matched, while the second parameter
contains the destination it should be routed to. In the above example, if the literal word
"product" is found in the first segment of the URL path, and a number is found in the second segment,
the ``Catalog`` class and the ``productLookup`` method are used instead.

Placeholders are simply strings that represent a Regular Expression pattern. During the routing
process, these placeholders are replaced with the value of the Regular Expression. They are primarily
used for readability.

The following placeholders are available for you to use in your routes:

============ ===========================================================================================================
Placeholders Description
============ ===========================================================================================================
(:any)       will match all characters from that point to the end of the URI. This may include multiple URI segments.
(:segment)   will match any character except for a forward slash (``/``) restricting the result to a single segment.
(:num)       will match any integer.
(:alpha)     will match any string of alphabetic characters
(:alphanum)  will match any string of alphabetic characters or integers, or any combination of the two.
(:hash)      is the same as ``(:segment)``, but can be used to easily see which routes use hashed ids.
============ ===========================================================================================================

.. note:: ``{locale}`` cannot be used as a placeholder or other part of the route, as it is reserved for use
    in :doc:`localization </outgoing/localization>`.

Examples
========

Here are a few basic routing examples.

A URL containing the word **journals** in the first segment will be remapped to the ``\App\Controllers\Blogs`` class,
and the default method, which is usually ``index()``:

.. literalinclude:: routing/006.php
   :lines: 2-

A URL containing the segments **blog/joe** will be remapped to the ``\App\Controllers\Blogs`` class and the ``users`` method.
The ID will be set to ``34``:

.. literalinclude:: routing/007.php
   :lines: 2-

A URL with **product** as the first segment, and anything in the second will be remapped to the ``\App\Controllers\Catalog`` class
and the ``productLookup`` method:

.. literalinclude:: routing/008.php
   :lines: 2-

A URL with **product** as the first segment, and a number in the second will be remapped to the ``\App\Controllers\Catalog`` class
and the ``productLookupByID`` method passing in the match as a variable to the method:

.. literalinclude:: routing/009.php
   :lines: 2-

Note that a single ``(:any)`` will match multiple segments in the URL if present. For example the route:

.. literalinclude:: routing/010.php
   :lines: 2-

will match **product/123**, **product/123/456**, **product/123/456/789** and so on. The implementation in the
Controller should take into account the maximum parameters:

.. literalinclude:: routing/011.php
   :lines: 2-

If matching multiple segments is not the intended behavior, ``(:segment)`` should be used when defining the
routes. With the examples URLs from above:

.. literalinclude:: routing/012.php
   :lines: 2-

will only match **product/123** and generate 404 errors for other example.

Custom Placeholders
===================

You can create your own placeholders that can be used in your routes file to fully customize the experience
and readability.

You add new placeholders with the ``addPlaceholder()`` method. The first parameter is the string to be used as
the placeholder. The second parameter is the Regular Expression pattern it should be replaced with.
This must be called before you add the route:

.. literalinclude:: routing/013.php
   :lines: 2-

Regular Expressions
===================

If you prefer you can use regular expressions to define your routing rules. Any valid regular expression
is allowed, as are back-references.

.. important:: Note: If you use back-references you must use the dollar syntax rather than the double backslash syntax.
    A typical RegEx route might look something like this:

    .. literalinclude:: routing/014.php
       :lines: 2-

In the above example, a URI similar to **products/shirts/123** would instead call the ``show`` method
of the ``Products`` controller class, with the original first and second segment passed as arguments to it.

With regular expressions, you can also catch a segment containing a forward slash (``/``), which would usually
represent the delimiter between multiple segments.

For example, if a user accesses a password protected area of your web application and you wish to be able to
redirect them back to the same page after they log in, you may find this example useful:

.. literalinclude:: routing/015.php
   :lines: 2-

For those of you who don’t know regular expressions and want to learn more about them,
`regular-expressions.info <https://www.regular-expressions.info/>`_ might be a good starting point.

.. important:: Note: You can also mix and match wildcards with regular expressions.

Closures
========

You can use an anonymous function, or Closure, as the destination that a route maps to. This function will be
executed when the user visits that URI. This is handy for quickly executing small tasks, or even just showing
a simple view:

.. literalinclude:: routing/016.php
   :lines: 2-

Mapping multiple routes
=======================

While the ``add()`` method is simple to use, it is often handier to work with multiple routes at once, using
the ``map()`` method. Instead of calling the ``add()`` method for each route that you need to add, you can
define an array of routes and then pass it as the first parameter to the ``map()`` method:

.. literalinclude:: routing/017.php
   :lines: 2-

Redirecting Routes
==================

Any site that lives long enough is bound to have pages that move. You can specify routes that should redirect
to other routes with the ``addRedirect()`` method. The first parameter is the URI pattern for the old route. The
second parameter is either the new URI to redirect to, or the name of a named route. The third parameter is
the HTTP status code that should be sent along with the redirect. The default value is ``302`` which is a temporary
redirect and is recommended in most cases:

.. literalinclude:: routing/018.php
   :lines: 2-

If a redirect route is matched during a page load, the user will be immediately redirected to the new page before a
controller can be loaded.

Grouping Routes
===============

You can group your routes under a common name with the ``group()`` method. The group name becomes a segment that
appears prior to the routes defined inside of the group. This allows you to reduce the typing needed to build out an
extensive set of routes that all share the opening string, like when building an admin area:

.. literalinclude:: routing/019.php
   :lines: 2-

This would prefix the **users** and **blog** URIs with **admin**, handling URLs like **admin/users** and **admin/blog**.

If you need to assign options to a group, like a :ref:`assigning-namespace`, do it before the callback:

.. literalinclude:: routing/020.php
   :lines: 2-

This would handle a resource route to the ``App\API\v1\Users`` controller with the **api/users** URI.

You can also use a specific :doc:`filter <filters>` for a group of routes. This will always
run the filter before or after the controller. This is especially handy during authentication or api logging:

.. literalinclude:: routing/021.php
   :lines: 2-

The value for the filter must match one of the aliases defined within **app/Config/Filters.php**.

It is possible to nest groups within groups for finer organization if you need it:

.. literalinclude:: routing/022.php
   :lines: 2-

This would handle the URL at **admin/users/list**.

.. note:: Options passed to the outer ``group()`` (for example ``namespace`` and ``filter``) are not merged with the inner ``group()`` options.

At some point, you may want to group routes for the purpose of applying filters or other route
config options like namespace, subdomain, etc. Without necessarily needing to add a prefix to the group, you can pass
an empty string in place of the prefix and the routes in the group will be routed as though the group never existed but with the
given route config options:

.. literalinclude:: routing/023.php
   :lines: 2-

Environment Restrictions
========================

You can create a set of routes that will only be viewable in a certain environment. This allows you to create
tools that only the developer can use on their local machines that are not reachable on testing or production servers.
This can be done with the ``environment()`` method. The first parameter is the name of the environment. Any
routes defined within this closure are only accessible from the given environment:

.. literalinclude:: routing/024.php
   :lines: 2-

Reverse Routing
===============

Reverse routing allows you to define the controller and method, as well as any parameters, that a link should go
to, and have the router lookup the current route to it. This allows route definitions to change without you having
to update your application code. This is typically used within views to create links.

For example, if you have a route to a photo gallery that you want to link to, you can use the ``route_to()`` helper
function to get the current route that should be used. The first parameter is the fully qualified Controller and method,
separated by a double colon (``::``), much like you would use when writing the initial route itself. Any parameters that
should be passed to the route are passed in next:

.. literalinclude:: routing/025.php
   :lines: 2-

Using Named Routes
==================

You can name routes to make your application less fragile. This applies a name to a route that can be called
later, and even if the route definition changes, all of the links in your application built with ``route_to()``
will still work without you having to make any changes. A route is named by passing in the ``as`` option
with the name of the route:

.. literalinclude:: routing/026.php
   :lines: 2-

This has the added benefit of making the views more readable, too.

Routes with any HTTP verbs
==========================

It is possible to define a route with any HTTP verbs.
You can use the ``add()`` method:

.. literalinclude:: routing/027.php
   :lines: 2-

.. warning:: While the ``add()`` method seems to be convenient, it is recommended to always use the HTTP-verb-based
    routes, described above, as it is more secure. If you use the :doc:`CSRF protection </libraries/security>`, it does not protect **GET**
    requests. If the URI specified in the ``add()`` method is accessible by the GET method, the CSRF protection
    will not work.

.. note:: Using the HTTP-verb-based routes will also provide a slight performance increase, since
    only routes that match the current request method are stored, resulting in fewer routes to scan through
    when trying to find a match.

.. _command-line-only-routes:

Command-Line Only Routes
========================

You can create routes that work only from the command-line, and are inaccessible from the web browser, with the
``cli()`` method. This is great for building cron jobs or CLI-only tools. Any route created by any of the HTTP-verb-based
route methods will also be inaccessible from the CLI, but routes created by the ``add()`` method will still be
available from the command line:

.. literalinclude:: routing/028.php
   :lines: 2-

.. warning:: If you don't disable auto-routing and place the command file in **app/Controllers**,
    anyone could access the command with the help of auto-routing via HTTP.

Global Options
==============

All of the methods for creating a route (add, get, post, :doc:`resource <restful>` etc) can take an array of options that
can modify the generated routes, or further restrict them. The ``$options`` array is always the last parameter:

.. literalinclude:: routing/029.php
   :lines: 2-

.. _applying-filters:

Applying Filters
----------------

You can alter the behavior of specific routes by supplying filters to run before or after the controller. This is especially handy during authentication or api logging.
The value for the filter can be a string or an array of strings:

* matching the aliases defined in **app/Config/Filters.php**.
* filter classnames

See :doc:`Controller filters <filters>` for more information on setting up filters.

.. Warning:: If you set filters to routes in **app/Config/Routes.php**
    (not in **app/Config/Filters.php**), it is recommended to disable auto-routing.
    When auto-routing is enabled, it may be possible that a controller can be accessed
    via a different URL than the configured route,
    in which case the filter you specified to the route will not be applied.
    See :ref:`use-defined-routes-only` to disable auto-routing.

**Alias filter**

You specify an alias defined in **app/Config/Filters.php** for the filter value:

.. literalinclude:: routing/030.php
   :lines: 2-

You may also supply arguments to be passed to the alias filter's ``before()`` and ``after()`` methods:

.. literalinclude:: routing/031.php
   :lines: 2-

**Classname filter**

You specify a filter classname for the filter value:

.. literalinclude:: routing/032.php
   :lines: 2-

**Multiple filters**

.. important:: *Multiple filters* is disabled by default. Because it breaks backward compatibility. If you want to use it, you need to configure. See :ref:`upgrade-415-multiple-filters-for-a-route` for the details.

You specify an array for the filter value:

.. literalinclude:: routing/033.php
   :lines: 2-

.. _assigning-namespace:

Assigning Namespace
-------------------

While a default namespace will be prepended to the generated controllers (see below), you can also specify
a different namespace to be used in any options array, with the ``namespace`` option. The value should be the
namespace you want modified:

.. literalinclude:: routing/034.php
   :lines: 2-

The new namespace is only applied during that call for any methods that create a single route, like get, post, etc.
For any methods that create multiple routes, the new namespace is attached to all routes generated by that function
or, in the case of ``group()``, all routes generated while in the closure.

Limit to Hostname
-----------------

You can restrict groups of routes to function only in certain domain or sub-domains of your application
by passing the "hostname" option along with the desired domain to allow it on as part of the options array:

.. literalinclude:: routing/035.php
   :lines: 2-

This example would only allow the specified hosts to work if the domain exactly matched **accounts.example.com**.
It would not work under the main site at **example.com**.

Limit to Subdomains
-------------------

When the ``subdomain`` option is present, the system will restrict the routes to only be available on that
sub-domain. The route will only be matched if the subdomain is the one the application is being viewed through:

.. literalinclude:: routing/036.php
   :lines: 2-

You can restrict it to any subdomain by setting the value to an asterisk, (``*``). If you are viewing from a URL
that does not have any subdomain present, this will not be matched:

.. literalinclude:: routing/037.php
   :lines: 2-

.. important:: The system is not perfect and should be tested for your specific domain before being used in production.
    Most domains should work fine but some edge case ones, especially with a period in the domain itself (not used
    to separate suffixes or www) can potentially lead to false positives.

Offsetting the Matched Parameters
---------------------------------

You can offset the matched parameters in your route by any numeric value with the ``offset`` option, with the
value being the number of segments to offset.

This can be beneficial when developing API's with the first URI segment being the version number. It can also
be used when the first parameter is a language string:

.. literalinclude:: routing/038.php
   :lines: 2-

.. _routing-priority:

Route processing queue
----------------------

When working with modules, it can be a problem if the routes in the application contain wildcards.
Then the module routes will not be processed correctly.
You can solve this problem by lowering the priority of route processing using the ``priority`` option. The parameter
accepts positive integers and zero. The higher the number specified in the ``priority``, the lower
route priority in the processing queue:

.. literalinclude:: routing/039.php
   :lines: 2-

To disable this functionality, you must call the method with the parameter ``false``:

.. literalinclude:: routing/040.php
   :lines: 2-

.. note:: By default, all routes have a priority of 0.
    Negative integers will be cast to the absolute value.

.. _routes-configuration-options:

****************************
Routes Configuration Options
****************************

The RoutesCollection class provides several options that affect all routes, and can be modified to meet your
application's needs. These options are available at the top of **app/Config/Routes.php**.

Default Namespace
=================

When matching a controller to a route, the router will add the default namespace value to the front of the controller
specified by the route. By default, this value is ``App\Controllers``.

If you set the value empty string (``''``), it leaves each route to specify the fully namespaced
controller:

.. literalinclude:: routing/041.php
   :lines: 2-

If your controllers are not explicitly namespaced, there is no need to change this. If you namespace your controllers,
then you can change this value to save typing:

.. literalinclude:: routing/042.php
   :lines: 2-

Default Controller
==================

When a user visits the root of your site (i.e., example.com) the controller to use is determined by the value set by
the ``setDefaultController()`` method, unless a route exists for it explicitly. The default value for this is ``Home``
which matches the controller at **app/Controllers/Home.php**:

.. literalinclude:: routing/043.php
   :lines: 2-

The default controller is also used when no matching route has been found, and the URI would point to a directory
in the controllers directory. For example, if the user visits **example.com/admin**, if a controller was found at
**app/Controllers/Admin/Home.php**, it would be used.

Default Method
==============

This works similar to the default controller setting, but is used to determine the default method that is used
when a controller is found that matches the URI, but no segment exists for the method. The default value is
``index``.

In this example, if the user were to visit **example.com/products**, and a ``Products`` controller existed, the
``Products::listAll()`` method would be executed:

.. literalinclude:: routing/044.php
   :lines: 2-

Translate URI Dashes
====================

This option enables you to automatically replace dashes (``-``) with underscores in the controller and method
URI segments, thus saving you additional route entries if you need to do that. This is required because the
dash isn’t a valid class or method name character and would cause a fatal error if you try to use it:

.. literalinclude:: routing/045.php
   :lines: 2-

.. _use-defined-routes-only:

Use Defined Routes Only
=======================

When no defined route is found that matches the URI, the system will attempt to match that URI against the
controllers and methods as described above. You can disable this automatic matching, and restrict routes
to only those defined by you, by setting the ``setAutoRoute()`` option to false:

.. literalinclude:: routing/046.php
   :lines: 2-

.. warning:: If you use the :doc:`CSRF protection </libraries/security>`, it does not protect **GET**
    requests. If the URI is accessible by the GET method, the CSRF protection will not work.

404 Override
============

When a page is not found that matches the current URI, the system will show a generic 404 view. You can change
what happens by specifying an action to happen with the ``set404Override()`` method. The value can be either
a valid class/method pair, just like you would show in any route, or a Closure:

.. literalinclude:: routing/047.php
   :lines: 2-

Route processing by priority
============================

Enables or disables processing of the routes queue by priority. Lowering the priority is defined in the route option.
Disabled by default. This functionality affects all routes.
For an example use of lowering the priority see :ref:`routing-priority`:

.. literalinclude:: routing/048.php
   :lines: 2-

*****************
Confirming Routes
*****************

CodeIgniter has the following :doc:`command </cli/cli_commands>` to display all routes.

.. _spark-routes:

**routes**

Displays all routes and filters::

    > php spark routes

The output is like the following:

.. code-block:: none

    +--------+------------------+------------------------------------------+----------------+-----------------------+
    | Method | Route            | Handler                                  | Before Filters | After Filters         |
    +--------+------------------+------------------------------------------+----------------+-----------------------+
    | GET    | /                | \App\Controllers\Home::index             | invalidchars   | secureheaders toolbar |
    | GET    | feed             | (Closure)                                | invalidchars   | secureheaders toolbar |
    | CLI    | ci(.*)           | \CodeIgniter\CLI\CommandRunner::index/$1 |                |                       |
    | auto   | /                | \App\Controllers\Home::index             | invalidchars   | secureheaders toolbar |
    | auto   | home             | \App\Controllers\Home::index             | invalidchars   | secureheaders toolbar |
    | auto   | home/index[/...] | \App\Controllers\Home::index             | invalidchars   | secureheaders toolbar |
    +--------+------------------+------------------------------------------+----------------+-----------------------+

The *Method* column shows the HTTP method that the route is listening for. ``auto`` means that the route is discovered by auto routing, so it is not defined in **app/Config/Routes.php**.

The *Route* column shows the URI path to match. The route of a defined route is expressed as a regular expression.
But ``[/...]`` in the route of an auto route is indicates any number of segments.

.. note:: When auto routing is enabled, if you have the route ``home``, it can be also accessd by ``Home``, or maybe by ``hOme``, ``hoMe``, ``HOME``, etc. But the command shows only ``home``.

.. important:: The system is not perfect. If you use Custom Placeholders, *Filters* might not be correct. But the filters defined in **app/Config/Routes.php** are always displayed correctly.