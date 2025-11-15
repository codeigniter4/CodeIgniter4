.. _auto-routing-improved:

#######################
Auto Routing (Improved)
#######################

.. versionadded:: 4.2.0

.. contents::
    :local:
    :depth: 3

*********************************
What is Auto Routing (Improved) ?
*********************************

By default, all routes must be :ref:`defined <defined-route-routing>` in the
configuration file.

However, with **Auto Routing (Improved)**, you can define the controller name and
its method name according to the convention and it will be automatically routed.
In other words, there is no need to define routes manually.

If you enable Auto Routing (Improved), when no defined route is found that matches
the URI, the system will attempt to match that URI against the controllers and
methods.

.. important:: For security reasons, if a controller is used in the defined routes,
    Auto Routing (Improved) does not route to the controller.

.. note:: Auto Routing (Improved) is disabled by default. To use it, see
    :ref:`enabled-auto-routing-improved`.

**************************************
Differences from Auto Routing (Legacy)
**************************************

:ref:`auto-routing-legacy` is a routing system from CodeIgniter 3. If you are not
familiar with it, go to the next section.

If you know it well, these are some changes in **Auto Routing (Improved)**:

- A controller method needs HTTP verb prefix like ``getIndex()``, ``postCreate()``.
    - Since developers always know the HTTP method, a request with an unexpected
      HTTP method will never execute the controller.
- The Default Controller (``Home`` by default) and the Default Method (``index`` by default) must be omitted in the URI.
    - It restricts one-to-one correspondence between controller methods and URIs.
    - E.g. by default, you can access ``/``, but ``/home`` and ``/home/index``
      will be 404 Not Found.
- It checks method parameter count.
    - If there are more parameters in the URI than the method parameters, it results
      in 404 Not Found.
- It does not support ``_remap()`` method.
    - It restricts one-to-one correspondence between controller methods and URIs.
    - But it has the :ref:`auto-routing-improved-default-method-fallback` feature
      instead.
- Can't access controllers in Defined Routes.
    - It completely separates controllers accessible via **Auto Routing** from
      those accessible via **Defined Routes**.

.. _enabled-auto-routing-improved:

******************************
Enable Auto Routing (Improved)
******************************

To use it, you need to change the setting ``$autoRoute`` option to ``true`` in
**app/Config/Routing.php**::

    public bool $autoRoute = true;

And you need to change the property ``$autoRoutesImproved`` to ``true`` in
**app/Config/Feature.php**::

    public bool $autoRoutesImproved = true;

.. important:: When you use Auto Routing (Improved), you must remove the line
    ``$routes->get('/', 'Home::index');``  in **app/Config/Routes.php**. Because
    defined routes take precedence over Auto Routing, and controllers defined in
    the defined routes are denied access by Auto Routing (Improved) for security
    reasons.

************
URI Segments
************

The segments in the URL, in following with the Model-View-Controller approach,
usually represent::

    http://example.com/{class}/{method}/{param1}

1. The first segment represents the controller **class** that should be invoked.
2. The second segment represents the class **method** that should be called.
3. The third, and any additional segments, represent any **parameters** that will be passed to the controller method.

Consider this URI::

    http://example.com/hello-world/hello/1

In the above example, when you send an HTTP request with **GET** method,
Auto Routing (Improved) would attempt to find a controller named
``App\Controllers\HelloWorld`` and executes ``getHello()`` method with passing
``'1'`` as the first parameter.

.. note:: A controller method that will be executed by Auto Routing (Improved)
    needs HTTP verb (``get``, ``post``, ``put``, etc.) prefix like ``getIndex()``,
    ``postCreate()``.

.. note:: When a controller's short name matches the first segment of a URI, it
    will be loaded.

**************************
Let's try it: Hello World!
**************************

Let's create a simple controller so you can see it in action.

Create a Controller
===================

Using your text editor, create a file called **HelloWorld.php** in your
**app/Controllers** directory, and put the following code in it.

.. literalinclude:: auto_routing_improved/020.php

.. important:: The file must be called **HelloWorld.php**. When you use Auto
    Routing (Improved), controller class names MUST be CamelCase.

You will notice that the ``HelloWorld`` Controller is extending the ``BaseController``.
You can also extend the ``CodeIgniter\Controller`` if you do not need the functionality
of the BaseController.

The BaseController provides a convenient place for loading components and performing
functions that are needed by all your controllers. You can extend this class in
any new controller.

.. important:: A controller method that will be executed by Auto Routing (Improved)
    needs HTTP verb (``get``, ``post``, ``put``, etc.) prefix like ``getIndex()``,
    ``postCreate()``.

Check the Routes
================

You can check your routes with the ``spark routes`` command.

.. code-block:: console

    php spark routes

If you did it right, you should see:

.. code-block:: none

    +-----------+-------------+------+---------------------------------------+----------------+---------------+
    | Method    | Route       | Name | Handler                               | Before Filters | After Filters |
    +-----------+-------------+------+---------------------------------------+----------------+---------------+
    | GET(auto) | hello-world |      | \App\Controllers\HelloWorld::getIndex |                |               |
    +-----------+-------------+------+---------------------------------------+----------------+---------------+

See :ref:`routing-spark-routes` for the output details.

Visit Your Site
===============

Now visit your site using a URL similar to this::

    http://example.com/hello-world

The system automatically translates URI with dashes (``-``) to CamelCase in the
controller and method URI segments.

For example, the URI ``sub-dir/hello-controller/some-method`` will execute the
``SubDir\HelloController::getSomeMethod()`` method.

If you did it right, you should see::

    Hello World!

****************************
Examples of Controller Names
****************************

The following is an valid controller name. Because ``App\Controllers\HelloWorld``
is CamelCase.

.. literalinclude:: auto_routing_improved/009.php

The following is **not** valid. Because the first letter (``h``) is not capital.

.. literalinclude:: auto_routing_improved/010.php

The following is also **not** valid. Because the first letter (``h``) is not capital.

.. literalinclude:: auto_routing_improved/011.php

******************
Controller Methods
******************

Method Visibility
=================

When you define a method that is executable via HTTP request, the method must be
declared as ``public``.

.. warning:: For security reasons be sure to declare any new utility methods as
    ``protected`` or ``private``.

Default Method
==============

In the above example, the method name is ``getIndex()``. The method
(HTTP verb + ``Index()``) is called the **Default Method**, and is loaded if the
**second segment** of the URI is empty.

Normal Methods
==============

The second segment of the URI determines which method in the controller gets called.

Let's try it. Add a new method to your controller:

.. literalinclude:: auto_routing_improved/021.php

Now load the following URL to see the ``getComment()`` method::

    http://example.com/hello-world/comment/

You should see your new message.

************************************
Passing URI Segments to Your Methods
************************************

If your URI contains more than two segments they will be passed to your
method as parameters.

For example, let's say you have a URI like this::

    http://example.com/products/shoes/sandals/123

Your method will be passed URI segments 3 and 4 (``'sandals'`` and ``'123'``):

.. literalinclude:: auto_routing_improved/022.php

.. note:: If there are more parameters in the URI than the method parameters,
    Auto Routing (Improved) does not execute the method, and it results in 404
    Not Found.

******************
Default Controller
******************

The **Default Controller** is a special controller that is used when a URI ends
with a directory name or when a URI is not present, as will be the case when only
your site root URL is requested.

By default, the Default Controller is ``Home``.

.. note:: Define only the default method (``getIndex()`` for GET requests)
    in the default controller. If you define any other public method, that method
    will not be executed.

For more information, please refer to the
:ref:`routing-auto-routing-improved-configuration-options`.

.. _auto-routing-improved-default-method-fallback:

***********************
Default Method Fallback
***********************

.. versionadded:: 4.4.0

If the controller method corresponding to the URI segment of the method name
does not exist, and if the default method is defined, the remaining URI segments
are passed to the default method for execution.

.. literalinclude:: controllers/024.php

Load the following URL::

    http://example.com/product/15/edit

The method will be passed URI segments 2 and 3 (``'15'`` and ``'edit'``):

.. important:: If there are more parameters in the URI than the method parameters,
    Auto Routing (Improved) does not execute the method, and it results in 404
    Not Found.

Fallback to Default Controller
==============================

If the controller corresponding to the URI segment of the controller name
does not exist, and if the default controller (``Home`` by default) exists in
the directory, the remaining URI segments are passed to the default controller's
default method.

For example, when you have the following default controller ``Home`` in the
**app/Controllers/News** directory:

.. literalinclude:: controllers/025.php

Load the following URL::

    http://example.com/news/101

The ``News\Home`` controller and the default ``getIndex()`` method will be found.
So the default method will get the second URI segment (``'101'``):

.. note:: If there is ``App\Controllers\News`` controller, it takes precedence.
    The URI segments are searched sequentially and the first controller found
    is used.

.. note:: If there are more parameters in the URI than the method parameters,
    Auto Routing (Improved) does not execute the method, and it results in 404
    Not Found.

************************************************
Organizing Your Controllers into Sub-directories
************************************************

If you are building a large application you might want to hierarchically
organize or structure your controllers into sub-directories. CodeIgniter
permits you to do this.

Simply create sub-directories under the main **app/Controllers**,
and place your controller classes within them.

.. important:: Directory names MUST start with an uppercase letter and be CamelCase.

When using this feature the first segment of your URI must
specify the directory. For example, let's say you have a controller located here::

    app/Controllers/Products/Shoes.php

To call the above controller your URI will look something like this::

    http://example.com/products/shoes/show/123

.. note:: You cannot have directories with the same name in **app/Controllers**
    and **public**.
    This is because if there is a directory, the web server will search for it and
    it will not be routed to CodeIgniter.

Each of your sub-directories may contain a default controller which will be
called if the URL contains *only* the sub-directory. Simply put a controller
in there that matches the name of your default controller as specified in
your **app/Config/Routing.php** file.

***************************************
Examples of Controller/Methods and URIs
***************************************

In the case of a **GET** request with the default configuration, the mapping
between controller/methods and URIs is as follows:

============================ ============================ =============================================
Controller/Method            URI                          Description
============================ ============================ =============================================
``Home::getIndex()``         /                            The default controller and the default method.
``Blog::getIndex()``         /blog                        The default method.
``UserProfile::getIndex()``  /user-profile                The default method.
``Blog::getTags()``          /blog/tags
``Blog::getNews($id)``       /blog/news/123
``Blog\Home::getIndex()``    /blog                        Sub-directory ``Blog`` and the default
                                                          controller and the default method. If there
                                                          is ``Blog`` controller, it takes precedence.
``Blog\Tags::getIndex()``    /blog/tags                   Sub-directory ``Blog`` and the default
                                                          method. If there is ``Blog`` controller, it
                                                          takes precedence.
``Blog\News::getIndex($id)`` /blog/news/123               Sub-directory ``Blog`` and the default method
                                                          fallback. If there is ``Blog`` controller, it
                                                          takes precedence.
============================ ============================ =============================================

****************
Applying Filters
****************

Applying controller filters allows you to add processing before and after the
controller method execution. This is especially handy during authentication or
api logging.

If you use Auto Routing, set the filters to be applied in **app/Config/Filters.php**.
See :doc:`Controller Filters <filters>` for more information on setting up filters.

.. _routing-auto-routing-improved-configuration-options:

*********************
Configuration Options
*********************

These options are available in the **app/Config/Routing.php** file.

Default Controller
==================

For Site Root URI
-----------------

When a user visits the root of your site (i.e., **http://example.com**) the controller
to use is determined by the value set to the ``$defaultController`` property,
unless a route exists for it explicitly.

The default value for this is ``Home`` which matches the controller at
**app/Controllers/Home.php**::

    public string $defaultController = 'Home';

For Directory URI
-----------------

The default controller is also used when no matching route has been found, and
the URI would point to a directory in the controllers directory. For example, if
the user visits **http://example.com/admin**, if a controller was found at
**app/Controllers/Admin/Home.php**, it would be used.

.. important:: You cannot access the default controller with the URI of the
    controller name. When the default controller is ``Home``, you can access
    **http://example.com/**, but if you access **http://example.com/home**, it
    will be not found.

.. _routing-auto-routing-improved-default-method:

Default Method
==============

This works similar to the default controller setting, but is used to determine
the default method that is used when a controller is found that matches the URI,
but no segment exists for the method. The default value is ``index``.

In this example, if the user were to visit **example.com/products**, and a ``Products``
controller existed, the ``Products::getListAll()`` method would be executed::

    public string $defaultMethod = 'listAll';

.. important:: You cannot access the controller with the URI of the default method
    name. In the example above, you can access **example.com/products**, but if
    you access **example.com/products/listall**, it will be not found.

.. _translate-uri-to-camelcase:

Translate URI To CamelCase
==========================

.. versionadded:: 4.5.0

.. note:: Since v4.6.0, the ``$translateUriToCamelCase`` option is enabled by
    default.

Since v4.5.0, the ``$translateUriToCamelCase`` option has been implemented,
which works well with the current CodeIgniter's coding standards.

This option enables you to automatically translate URI with dashes (``-``) to
CamelCase in the controller and method URI segments.

For example, the URI ``sub-dir/hello-controller/some-method`` will execute the
``SubDir\HelloController::getSomeMethod()`` method.

.. note:: When this option is enabled, the ``$translateURIDashes`` option is
    ignored.

Disable Translate URI To CamelCase
----------------------------------

.. note:: The option to disable "Translate URI To CamelCase" exists only for
    backward compatibility. We don't recommend to disable it.

To disable it, you need to change the setting ``$translateUriToCamelCase`` option
to ``false`` in **app/Config/Routing.php**::

    public bool $translateUriToCamelCase = false;

.. _auto-routing-improved-module-routing:

**************
Module Routing
**************

.. versionadded:: 4.4.0

You can use auto routing even if you use :doc:`../general/modules` and place
the controllers in a different namespace.

To route to a module, the ``$moduleRoutes`` property in **app/Config/Routing.php**
must be set::

    public array $moduleRoutes = [
        'blog' => 'Acme\Blog\Controllers',
    ];

The key is the first URI segment for the module, and the value is the controller
namespace. In the above configuration, **http://localhost:8080/blog/foo/bar**
will be routed to ``Acme\Blog\Controllers\Foo::getBar()``.

.. note:: If you define ``$moduleRoutes``, the routing for the module takes
    precedence. In the above example, even if you have the ``App\Controllers\Blog``
    controller, **http://localhost:8080/blog** will be routed to the default
    controller ``Acme\Blog\Controllers\Home``.
