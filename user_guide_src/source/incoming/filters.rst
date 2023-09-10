##################
Controller Filters
##################

.. contents::
    :local:
    :depth: 2

Controller Filters allow you to perform actions either before or after the controllers execute. Unlike :doc:`events <../extending/events>`,
you can choose the specific URIs or routes in which the filters will be applied to. Before filters may
modify the Request while after filters can act on and even modify the Response, allowing for a lot of flexibility
and power.

Some common examples of tasks that might be performed with filters are:

* Performing CSRF protection on the incoming requests
* Restricting areas of your site based upon their Role
* Perform rate limiting on certain endpoints
* Display a "Down for Maintenance" page
* Perform automatic content negotiation
* and more...

*****************
Creating a Filter
*****************

Filters are simple classes that implement ``CodeIgniter\Filters\FilterInterface``.
They contain two methods: ``before()`` and ``after()`` which hold the code that
will run before and after the controller respectively. Your class must contain both methods
but may leave the methods empty if they are not needed. A skeleton filter class looks like:

.. literalinclude:: filters/001.php

Before Filters
==============

Replacing Request
-----------------

From any filter, you can return the ``$request`` object and it will replace the current Request, allowing you
to make changes that will still be present when the controller executes.

Stopping Later Filters
----------------------

Also, when you have a series of filters you may also want to
stop the execution of the later filters after a certain filter. You can easily do this by returning
**any non-empty** result. If the before filter returns an empty result, the controller actions or the later
filters will still be executed.

An exception to the non-empty result rule is the ``Request`` instance.
Returning it in the before filter will not stop the execution but only replace the current ``$request`` object.

Returning Response
------------------

Since before filters are executed prior to your controller being executed, you may at times want to stop the
actions in the controller from happening.

This is typically used to perform redirects, like in this example:

.. literalinclude:: filters/002.php

If a ``Response`` instance is returned, the Response will be sent back to the client and script execution will stop.
This can be useful for implementing rate limiting for APIs. See :doc:`Throttler <../libraries/throttler>` for an
example.

.. _after-filters:

After Filters
=============

After filters are nearly identical to before filters, except that you can only return the ``$response`` object,
and you cannot stop script execution. This does allow you to modify the final output, or simply do something with
the final output. This could be used to ensure certain security headers were set the correct way, or to cache
the final output, or even to filter the final output with a bad words filter.

*******************
Configuring Filters
*******************

There are two ways to configure filters when they get run. One is done in
**app/Config/Filters.php**, the other is done in **app/Config/Routes.php**.

If you want to specify filter to a specific route, use **app/Config/Routes.php**
and see :ref:`URI Routing <applying-filters>`.

The filters that are specified to a route (in **app/Config/Routes.php**) are
executed before the filters specified in **app/Config/Filters.php**.

.. Note:: The safest way to apply filters is to :ref:`disable auto-routing <use-defined-routes-only>`, and :ref:`set filters to routes <applying-filters>`.

The **app/Config/Filters.php** file contains four properties that allow you to
configure exactly when the filters run.

.. Warning:: It is recommended that you should always add ``*`` at the end of a URI in the filter settings.
    Because a controller method might be accessible by different URLs than you think.
    For example, when :ref:`auto-routing-legacy` is enabled, if you have ``Blog::index``,
    it can be accessible with ``blog``, ``blog/index``, and ``blog/index/1``, etc.

$aliases
========

The ``$aliases`` array is used to associate a simple name with one or more fully-qualified class names that are the
filters to run:

.. literalinclude:: filters/003.php

Aliases are mandatory and if you try to use a full class name later, the system will throw an error. Defining them
in this way makes it simple to switch out the class used. Great for when you decided you need to change to a
different authentication system since you only change the filter's class and you're done.

You can combine multiple filters into one alias, making complex sets of filters simple to apply:

.. literalinclude:: filters/004.php

You should define as many aliases as you need.

$globals
========

The second section allows you to define any filters that should be applied to every request made by the framework.
You should take care with how many you use here, since it could have performance implications to have too many
run on every request. Filters can be specified by adding their alias to either the before or after array:

.. literalinclude:: filters/005.php

Except for a Few URIs
---------------------

There are times where you want to apply a filter to almost every request, but have a few that should be left alone.
One common example is if you need to exclude a few URI's from the CSRF protection filter to allow requests from
third-party websites to hit one or two specific URI's, while keeping the rest of them protected. To do this, add
an array with the ``except`` key and a URI path (relative to BaseURL) to match as the value alongside the alias:

.. literalinclude:: filters/006.php

Any place you can use a URI path (relative to BaseURL) in the filter settings, you can use a regular expression or, like in this example, use
an asterisk (``*``) for a wildcard that will match all characters after that. In this example, any URI path starting with ``api/``
would be exempted from CSRF protection, but the site's forms would all be protected. If you need to specify multiple
URI paths, you can use an array of URI path patterns:

.. literalinclude:: filters/007.php

$methods
========

.. Warning:: If you use ``$methods`` filters, you should :ref:`disable Auto Routing (Legacy) <use-defined-routes-only>`
    because :ref:`auto-routing-legacy` permits any HTTP method to access a controller.
    Accessing the controller with a method you don't expect could bypass the filter.

You can apply filters to all requests of a certain HTTP method, like POST, GET, PUT, etc. In this array, you would
specify the method name in **lowercase**. It's value would be an array of filters to run:

.. literalinclude:: filters/008.php

.. note:: Unlike the ``$globals`` or the
    ``$filters`` properties, these will only run as before filters.

In addition to the standard HTTP methods, this also supports one special case: ``cli``. The ``cli`` method would apply to
all requests that were run from the command line.

$filters
========

This property is an array of filter aliases. For each alias, you can specify ``before`` and ``after`` arrays that contain
a list of URI path (relative to BaseURL) patterns that filter should apply to:

.. literalinclude:: filters/009.php

.. _filters-filters-filter-arguments:

Filter Arguments
----------------

.. versionadded:: 4.4.0

When configuring ``$filters``, additional arguments may be passed to a filter:

.. literalinclude:: filters/012.php

In this example, when the URI matches ``admin/*'``, the array ``['admin', 'superadmin']``
will be passed in ``$arguments`` to the ``group`` filter's ``before()`` methods.
When the URI matches ``admin/users/*'``, the array ``['users.manage']``
will be passed in ``$arguments`` to the ``permission`` filter's ``before()`` methods.

******************
Confirming Filters
******************

CodeIgniter has the following :doc:`command <../cli/spark_commands>` to check the filters for a route.

.. _spark-filter-check:

filter:check
============

.. versionadded:: 4.3.0

Check the filters for the route ``/`` with **GET** method:

.. code-block:: console

    php spark filter:check get /

The output is like the following:

.. code-block:: none

    +--------+-------+----------------+---------------+
    | Method | Route | Before Filters | After Filters |
    +--------+-------+----------------+---------------+
    | GET    | /     |                | toolbar       |
    +--------+-------+----------------+---------------+

You can also see the routes and filters by the ``spark routes`` command.
See :ref:`URI Routing <routing-spark-routes>`.

****************
Provided Filters
****************

The filters bundled with CodeIgniter4 are: :doc:`Honeypot <../libraries/honeypot>`, :ref:`CSRF <cross-site-request-forgery>`, ``InvalidChars``, ``SecureHeaders``, and :ref:`DebugToolbar <the-debug-toolbar>`.

.. note:: The filters are executed in the order defined in the config file. However, if enabled, ``DebugToolbar`` is always executed last because it should be able to capture everything that happens in the other filters.

.. _invalidchars:

InvalidChars
=============

This filter prohibits user input data (``$_GET``, ``$_POST``, ``$_COOKIE``, ``php://input``) from containing the following characters:

- invalid UTF-8 characters
- control characters except line break and tab code

.. _secureheaders:

SecureHeaders
=============

This filter adds HTTP response headers that your application can use to increase the security of your application.

If you want to customize the headers, extend ``CodeIgniter\Filters\SecureHeaders`` and override the ``$headers`` property. And change the ``$aliases`` property in **app/Config/Filters.php**:

.. literalinclude:: filters/011.php

If you want to know about secure headers, see `OWASP Secure Headers Project <https://owasp.org/www-project-secure-headers/>`_.
