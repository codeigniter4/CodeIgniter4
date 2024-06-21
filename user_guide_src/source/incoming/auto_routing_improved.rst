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

This section describes the functionality of the new auto-routing.
It automatically routes an HTTP request, and executes the corresponding controller method
without route definitions.

Consider this URI::

    example.com/index.php/helloworld/

In the above example, CodeIgniter would attempt to find a controller named ``App\Controllers\Helloworld`` and load it, when auto-routing is enabled.

.. note:: When a controller's short name matches the first segment of a URI, it will be loaded.

Let's try it: Hello World!
==========================

Let's create a simple controller so you can see it in action. Using your text editor, create a file called **Helloworld.php**,
and put the following code in it. You will notice that the ``Helloworld`` Controller is extending the ``BaseController``. you can
also extend the ``CodeIgniter\Controller`` if you do not need the functionality of the BaseController.

The BaseController provides a convenient place for loading components and performing functions that are needed by all your
controllers. You can extend this class in any new controller.

.. literalinclude:: controllers/020.php

Then save the file to your **app/Controllers** directory.

.. important:: The file must be called **Helloworld.php**, with a capital ``H``. When you use Auto Routing, Controller class names MUST start with an uppercase letter and ONLY the first character can be uppercase.

    Since v4.5.0, if you enable the ``$translateUriToCamelCase`` option, you can
    use CamelCase classnames. See :ref:`controller-translate-uri-to-camelcase`
    for details.

.. important:: A controller method that will be executed by Auto Routing (Improved) needs HTTP verb (``get``, ``post``, ``put``, etc.) prefix like ``getIndex()``, ``postCreate()``.

Now visit your site using a URL similar to this::

    example.com/index.php/helloworld

If you did it right you should see::

    Hello World!

This is valid:

.. literalinclude:: controllers/009.php

This is **not** valid:

.. literalinclude:: controllers/010.php

This is **not** valid:

.. literalinclude:: controllers/011.php

.. note:: Since v4.5.0, if you enable the ``$translateUriToCamelCase`` option,
    you can use CamelCase classnames like above. See
    :ref:`controller-translate-uri-to-camelcase` for details.

Also, always make sure your controller extends the parent controller
class so that it can inherit all its methods.

.. note::
    The system will attempt to match the URI against Controllers by matching each segment against
    directories/files in **app/Controllers**, when a match wasn't found against defined routes.
    That's why your directories/files MUST start with a capital letter and the rest MUST be lowercase.

    If you want another naming convention you need to manually define it using the
    :ref:`Defined Route Routing <defined-route-routing>`.
    Here is an example based on PSR-4 Autoloader:

    .. literalinclude:: controllers/012.php

Methods
=======

Method Visibility
-----------------

When you define a method that is executable via HTTP request, the method must be
declared as ``public``.

.. warning:: For security reasons be sure to declare any new utility methods as ``protected`` or ``private``.

Default Method
--------------

In the above example, the method name is ``getIndex()``.
The method (HTTP verb + ``Index()``) is called the **default method**, and is loaded if the **second segment** of the URI is empty.

Normal Methods
--------------

The second segment of the URI determines which method in the
controller gets called.

Let's try it. Add a new method to your controller:

.. literalinclude:: controllers/021.php

Now load the following URL to see the ``getComment()`` method::

    example.com/index.php/helloworld/comment/

You should see your new message.

Passing URI Segments to Your Methods
====================================

If your URI contains more than two segments they will be passed to your
method as parameters.

For example, let's say you have a URI like this::

    example.com/index.php/products/shoes/sandals/123

Your method will be passed URI segments 3 and 4 (``'sandals'`` and ``'123'``):

.. literalinclude:: controllers/022.php

Default Controller
==================

The Default Controller is a special controller that is used when a URI ends with
a directory name or when a URI is not present, as will be the case when only your
site root URL is requested.

Defining a Default Controller
-----------------------------

Let's try it with the ``Helloworld`` controller.

To specify a default controller open your **app/Config/Routing.php**
file and set this property::

    public string $defaultController = 'Helloworld';

Where ``Helloworld`` is the name of the controller class you want to be used.

And comment out the line in **app/Config/Routes.php**:

.. literalinclude:: controllers/016.php
    :lines: 2-

If you now browse to your site without specifying any URI segments you'll
see the "Hello World" message.

.. important:: When you use Auto Routing (Improved), you must remove the line
    ``$routes->get('/', 'Home::index');``. Because defined routes take
    precedence over Auto Routing, and controllers defined in the defined routes
    are denied access by Auto Routing (Improved) for security reasons.

For more information, please refer to the
:ref:`routing-auto-routing-improved-configuration-options` documentation.

.. _controller-default-method-fallback:

Default Method Fallback
=======================

.. versionadded:: 4.4.0

If the controller method corresponding to the URI segment of the method name
does not exist, and if the default method is defined, the remaining URI segments
are passed to the default method for execution.

.. literalinclude:: controllers/024.php

Load the following URL::

    example.com/index.php/product/15/edit

The method will be passed URI segments 2 and 3 (``'15'`` and ``'edit'``):

.. important:: If there are more parameters in the URI than the method parameters,
    Auto Routing (Improved) does not execute the method, and it results in 404
    Not Found.

Fallback to Default Controller
------------------------------

If the controller corresponding to the URI segment of the controller name
does not exist, and if the default controller (``Home`` by default) exists in
the directory, the remaining URI segments are passed to the default controller's
default method.

For example, when you have the following default controller ``Home`` in the
**app/Controllers/News** directory:

.. literalinclude:: controllers/025.php

Load the following URL::

    example.com/index.php/news/101

The ``News\Home`` controller and the default ``getIndex()`` method will be found.
So the default method will be passed URI segments 2 (``'101'``):

.. note:: If there is ``App\Controllers\News`` controller, it takes precedence.
    The URI segments are searched sequentially and the first controller found
    is used.

.. note:: If there are more parameters in the URI than the method parameters,
    Auto Routing (Improved) does not execute the method, and it results in 404
    Not Found.

Organizing Your Controllers into Sub-directories
================================================

If you are building a large application you might want to hierarchically
organize or structure your controllers into sub-directories. CodeIgniter
permits you to do this.

Simply create sub-directories under the main **app/Controllers**,
and place your controller classes within them.

.. important:: Directory names MUST start with an uppercase letter and ONLY the first character can be uppercase.

    Since v4.5.0, if you enable the ``$translateUriToCamelCase`` option, you can
    use CamelCase directory names. See :ref:`controller-translate-uri-to-camelcase`
    for details.

When using this feature the first segment of your URI must
specify the directory. For example, let's say you have a controller located here::

    app/Controllers/Products/Shoes.php

To call the above controller your URI will look something like this::

    example.com/index.php/products/shoes/show/123

.. note:: You cannot have directories with the same name in **app/Controllers**
    and **public**.
    This is because if there is a directory, the web server will search for it and
    it will not be routed to CodeIgniter.

Each of your sub-directories may contain a default controller which will be
called if the URL contains *only* the sub-directory. Simply put a controller
in there that matches the name of your default controller as specified in
your **app/Config/Routing.php** file.

CodeIgniter also permits you to map your URIs using its :ref:`Defined Route Routing <defined-route-routing>`..

.. _controller-translate-uri-to-camelcase:

Translate URI To CamelCase
==========================

.. versionadded:: 4.5.0

Since v4.5.0, the ``$translateUriToCamelCase`` option has been implemented,
which works well with the current CodeIgniter's coding standards.

This option enables you to automatically translate URI with dashes (``-``) to
CamelCase in the controller and method URI segments.

For example, the URI ``sub-dir/hello-controller/some-method`` will execute the
``SubDir\HelloController::getSomeMethod()`` method.

.. note:: When this option is enabled, the ``$translateURIDashes`` option is
    ignored.

Enable Translate URI To CamelCase
---------------------------------

To enable it, you need to change the setting ``$translateUriToCamelCase`` option
to ``true`` in **app/Config/Routing.php**::

    public bool $translateUriToCamelCase = true;

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
