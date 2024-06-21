#######################
Auto Routing (Improved)
#######################

.. versionadded:: 4.2.0

.. contents::
    :local:
    :depth: 3

.. _auto-routing-improved:

Since v4.2.0, the new more secure Auto Routing has been introduced.

.. note:: If you are familiar with Auto Routing, which was enabled by default
    from CodeIgniter 3.x through 4.1.x, you can see the differences in
    :ref:`ChangeLog v4.2.0 <v420-new-improved-auto-routing>`.

When no defined route is found that matches the URI, the system will attempt to match that URI against the controllers and methods when Auto Routing is enabled.

.. important:: For security reasons, if a controller is used in the defined routes, Auto Routing (Improved) does not route to the controller.

Auto Routing can automatically route HTTP requests based on conventions
and execute the corresponding controller methods.

.. note:: Auto Routing (Improved) is disabled by default. To use it, see below.

.. _enabled-auto-routing-improved:

Enable Auto Routing
===================

To use it, you need to change the setting ``$autoRoute`` option to ``true`` in **app/Config/Routing.php**::

    public bool $autoRoute = true;

And you need to change the property ``$autoRoutesImproved`` to ``true`` in **app/Config/Feature.php**::

    public bool $autoRoutesImproved = true;

URI Segments
============

The segments in the URL, in following with the Model-View-Controller approach, usually represent::

    example.com/class/method/ID

1. The first segment represents the controller **class** that should be invoked.
2. The second segment represents the class **method** that should be called.
3. The third, and any additional segments, represent the ID and any variables that will be passed to the controller.

Consider this URI::

    example.com/index.php/helloworld/hello/1

In the above example, when you send an HTTP request with **GET** method,
Auto Routing would attempt to find a controller named ``App\Controllers\Helloworld``
and executes ``getHello()`` method with passing ``'1'`` as the first argument.

.. note:: A controller method that will be executed by Auto Routing (Improved) needs HTTP verb (``get``, ``post``, ``put``, etc.) prefix like ``getIndex()``, ``postCreate()``.

See :ref:`Auto Routing in Controllers <controller-auto-routing-improved>` for more info.

.. _routing-auto-routing-improved-configuration-options:

Configuration Options
=====================

These options are available in the **app/Config/Routing.php** file.

Default Controller
------------------

For Site Root URI
^^^^^^^^^^^^^^^^^

When a user visits the root of your site (i.e., **example.com**) the controller
to use is determined by the value set to the ``$defaultController`` property,
unless a route exists for it explicitly.

The default value for this is ``Home`` which matches the controller at
**app/Controllers/Home.php**::

    public string $defaultController = 'Home';

For Directory URI
^^^^^^^^^^^^^^^^^

The default controller is also used when no matching route has been found, and the URI would point to a directory
in the controllers directory. For example, if the user visits **example.com/admin**, if a controller was found at
**app/Controllers/Admin/Home.php**, it would be used.

.. important:: You cannot access the default controller with the URI of the controller name.
    When the default controller is ``Home``, you can access **example.com/**, but if you access **example.com/home**, it will be not found.

See :ref:`Auto Routing in Controllers <controller-auto-routing-improved>` for more info.

.. _routing-auto-routing-improved-default-method:

Default Method
--------------

This works similar to the default controller setting, but is used to determine the default method that is used
when a controller is found that matches the URI, but no segment exists for the method. The default value is
``index``.

In this example, if the user were to visit **example.com/products**, and a ``Products``
controller existed, the ``Products::getListAll()`` method would be executed::

    public string $defaultMethod = 'listAll';

.. important:: You cannot access the controller with the URI of the default method name.
    In the example above, you can access **example.com/products**, but if you access **example.com/products/listall**, it will be not found.

.. _auto-routing-improved-module-routing:


Module Routing
==============

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
