###########
Controllers
###########

Controllers are the heart of your application, as they determine how HTTP requests should be handled.

.. contents::
    :local:
    :depth: 2

What is a Controller?
*********************

A Controller is simply a class file that handles an HTTP request.
:doc:`URI Routing <routing>` associates a URI with a controller. It returns a
view string or ``Response`` object.

Every controller you create should extend ``BaseController`` class.
This class provides several features that are available to all of your controllers.

.. _controller-constructor:

Constructor
***********

The CodeIgniter's Controller has a special constructor ``initController()``.
It will be called by the framework after PHP's constructor ``__construct()`` execution.

If you want to override the ``initController()``, don't forget to add ``parent::initController($request, $response, $logger);`` in the method:

.. literalinclude:: controllers/023.php

.. important:: You cannot use ``return`` in the constructor. So ``return redirect()->to('route');`` does not work.

The ``initController()`` method sets the following three properties.

Included Properties
*******************

The CodeIgniter's Controller provides these properties.

Request Object
==============

The application's main :doc:`Request Instance </incoming/incomingrequest>` is always available
as a class property, ``$this->request``.

Response Object
===============

The application's main :doc:`Response Instance </outgoing/response>` is always available
as a class property, ``$this->response``.

Logger Object
=============

An instance of the :doc:`Logger <../general/logging>` class is available as a class property,
``$this->logger``.

.. _controllers-helpers:

Helpers
=======

You can define an array of helper files as a class property. Whenever the controller is loaded
these helper files will be automatically loaded into memory so that you can use their methods anywhere
inside the controller:

.. literalinclude:: controllers/001.php

forceHTTPS
**********

A convenience method for forcing a method to be accessed via HTTPS is available within all
controllers:

.. literalinclude:: controllers/002.php

By default, and in modern browsers that support the HTTP Strict Transport Security header, this
call should force the browser to convert non-HTTPS calls to HTTPS calls for one year. You can
modify this by passing the duration (in seconds) as the first parameter:

.. literalinclude:: controllers/003.php

.. note:: A number of :doc:`time-based constants </general/common_functions>` are always available for you to use, including ``YEAR``, ``MONTH``, and more.

.. _controllers-validating-data:

Validating Data
***************

.. _controller-validatedata:

$this->validateData()
=====================

.. versionadded:: 4.2.0

To simplify data checking, the controller also provides the convenience method
``validateData()``.

The method accepts (1) an array of data to validate, (2) an array of rules,
(3) an optional array of custom error messages to display if the items are not valid,
(4) an optional database group to use.

The :doc:`Validation Library docs </libraries/validation>` have details on
rule and message array formats, as well as available rules:

.. literalinclude:: controllers/006.php

.. _controller-validate:

$this->validate()
=================

.. important:: This method exists only for backward compatibility. Do not use it
    in new projects. Even if you are already using it, we recommend that you use
    the ``validateData()`` method instead.

The controller also provides the convenience method ``validate()``.

.. warning:: Instead of ``validate()``, use ``validateData()`` to validate POST
    data only. ``validate()`` uses ``$request->getVar()`` which returns
    ``$_GET``, ``$_POST`` or ``$_COOKIE`` data in that order (depending on php.ini
    `request-order <https://www.php.net/manual/en/ini.core.php#ini.request-order>`_).
    Newer values override older values. POST values may be overridden by the
    cookies if they have the same name.

The method accepts an array of rules in the first parameter,
and in the optional second parameter, an array of custom error messages to display
if the items are not valid.

Internally, this uses the controller's
``$this->request`` instance to get the data to be validated.

The :doc:`Validation Library docs </libraries/validation>` have details on
rule and message array formats, as well as available rules:

.. literalinclude:: controllers/004.php

.. warning:: When you use the ``validate()`` method, you should use the
    :ref:`getValidated() <validation-getting-validated-data>` method to get the
    validated data. Because the ``validate()`` method uses the
    :ref:`Validation::withRequest() <validation-withrequest>` method internally,
    and it validates data from
    :ref:`$request->getJSON() <incomingrequest-getting-json-data>`
    or :ref:`$request->getRawInput() <incomingrequest-retrieving-raw-data>`
    or :ref:`$request->getVar() <incomingrequest-getting-data>`, and an attacker
    could change what data is validated.

.. note:: The :ref:`$this->validator->getValidated() <validation-getting-validated-data>`
    method can be used since v4.4.0.

If you find it simpler to keep the rules in the configuration file, you can replace
the ``$rules`` array with the name of the group as defined in **app/Config/Validation.php**:

.. literalinclude:: controllers/005.php

.. note:: Validation can also be handled automatically in the model, but sometimes it's easier to do it in the controller. Where is up to you.

Protecting Methods
******************

In some cases, you may want certain methods hidden from public access.
To achieve this, simply declare the method as ``private`` or ``protected``.
That will prevent it from being served by a URL request.

For example, if you were to define a method like this for the ``Helloworld`` controller:

.. literalinclude:: controllers/007.php

and to define a route (``helloworld/utitilty``) for the method. Then trying to access it using the following URL will not work::

    example.com/index.php/helloworld/utility

Auto-routing also will not work.

.. _controller-auto-routing-improved:

Auto Routing (Improved)
************************

.. versionadded:: 4.2.0

Auto Routing (Improved) is a new, more secure automatic routing system.

See :doc:`auto_routing_improved` for details.

.. _controller-auto-routing-legacy:

Auto Routing (Legacy)
*********************

.. important:: This feature exists only for backward compatibility. Do not use it
    in new projects. Even if you are already using it, we recommend that you use
    the :ref:`auto-routing-improved` instead.

This section describes the functionality of Auto Routing (Legacy) that is a routing system from CodeIgniter 3.
It automatically routes an HTTP request, and executes the corresponding controller method
without route definitions. The auto-routing is disabled by default.

.. warning:: To prevent misconfiguration and miscoding, we recommend that you do not use
    Auto Routing (Legacy). It is easy to create vulnerable apps where controller filters
    or CSRF protection are bypassed.

.. important:: Auto Routing (Legacy) routes an HTTP request with **any** HTTP method to a controller method.

.. important:: Since v4.5.0, if Auto Routing (Legacy) doesn't find the controller,
    it will throw ``PageNotFoundException`` exception before the Controller Filters
    execute.

Consider this URI::

    example.com/index.php/helloworld/

In the above example, CodeIgniter would attempt to find a controller named **Helloworld.php** and load it.

.. note:: When a controller's short name matches the first segment of a URI, it will be loaded.

Let's try it: Hello World! (Legacy)
===================================

Let's create a simple controller so you can see it in action. Using your text editor, create a file called **Helloworld.php**,
and put the following code in it. You will notice that the ``Helloworld`` Controller is extending the ``BaseController``. you can
also extend the ``CodeIgniter\Controller`` if you do not need the functionality of the BaseController.

The BaseController provides a convenient place for loading components and performing functions that are needed by all your
controllers. You can extend this class in any new controller.

For security reasons be sure to declare any new utility methods as ``protected`` or ``private``:

.. literalinclude:: controllers/008.php

Then save the file to your **app/Controllers** directory.

.. important:: The file must be called **Helloworld.php**, with a capital ``H``. When you use Auto Routing, Controller class names MUST start with an uppercase letter and ONLY the first character can be uppercase.

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

Methods (Legacy)
================

In the above example, the method name is ``index()``. The ``index()`` method
is always loaded by default if the **second segment** of the URI is
empty. Another way to show your "Hello World" message would be this::

    example.com/index.php/helloworld/index/

**The second segment of the URI determines which method in the
controller gets called.**

Let's try it. Add a new method to your controller:

.. literalinclude:: controllers/013.php

Now load the following URL to see the comment method::

    example.com/index.php/helloworld/comment/

You should see your new message.

Passing URI Segments to Your Methods (Legacy)
=============================================

If your URI contains more than two segments they will be passed to your
method as parameters.

For example, let's say you have a URI like this::

    example.com/index.php/products/shoes/sandals/123

Your method will be passed URI segments 3 and 4 (``'sandals'`` and ``'123'``):

.. literalinclude:: controllers/014.php

Default Controller (Legacy)
===========================

The Default Controller is a special controller that is used when a URI end with
a directory name or when a URI is not present, as will be the case when only your
site root URL is requested.

Defining a Default Controller (Legacy)
--------------------------------------

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

.. note:: The line ``$routes->get('/', 'Home::index');`` is an optimization that you will want to use in a "real-world" app. But for demonstration purposes we don't want to use that feature. ``$routes->get()`` is explained in :doc:`URI Routing <routing>`

For more information, please refer to the the
:ref:`routing-auto-routing-legacy-configuration-options` documentation.

Organizing Your Controllers into Sub-directories (Legacy)
=========================================================

If you are building a large application you might want to hierarchically
organize or structure your controllers into sub-directories. CodeIgniter
permits you to do this.

Simply create sub-directories under the main **app/Controllers**,
and place your controller classes within them.

.. important:: Directory names MUST start with an uppercase letter and ONLY the first character can be uppercase.

When using this feature the first segment of your URI must
specify the directory. For example, let's say you have a controller located here::

    app/Controllers/Products/Shoes.php

To call the above controller your URI will look something like this::

    example.com/index.php/products/shoes/show/123

.. note:: You cannot have directories with the same name in **app/Controllers** and **public/**.
    This is because if there is a directory, the web server will search for it and
    it will not be routed to CodeIgniter.

Each of your sub-directories may contain a default controller which will be
called if the URL contains *only* the sub-directory. Simply put a controller
in there that matches the name of your default controller as specified in
your **app/Config/Routing.php** file.

CodeIgniter also permits you to map your URIs using its :ref:`Defined Route Routing <defined-route-routing>`..

Remapping Method Calls
**********************

.. note:: **Auto Routing (Improved)** does not support this feature intentionally.

As noted above, the second segment of the URI typically determines which
method in the controller gets called. CodeIgniter permits you to override
this behavior through the use of the ``_remap()`` method:

.. literalinclude:: controllers/017.php

.. important:: If your controller contains a method named ``_remap()``,
    it will **always** get called regardless of what your URI contains. It
    overrides the normal behavior in which the URI determines which method
    is called, allowing you to define your own method routing rules.

The overridden method call (typically the second segment of the URI) will
be passed as a parameter to the ``_remap()`` method:

.. literalinclude:: controllers/018.php

Any extra segments after the method name are passed into ``_remap()``. These parameters can be passed to the method
to emulate CodeIgniter's default behavior.

Example:

.. literalinclude:: controllers/019.php

Extending the Controller
************************

If you want to extend the controller, see :doc:`../extending/basecontroller`.

That's it!
**********

That, in a nutshell, is all there is to know about controllers.
