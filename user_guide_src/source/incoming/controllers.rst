###########
Controllers
###########

Controllers are the heart of your application, as they determine how HTTP requests should be handled.

.. contents::
    :local:
    :depth: 2

What is a Controller?
*********************

A Controller is simply a class file that handles a HTTP request. :doc:`URI Routing <routing>` associates a URI with a controller.

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

.. _controller-validate:

$this->validate()
=================

To simplify data checking, the controller also provides the convenience method ``validate()``.
The method accepts an array of rules in the first parameter,
and in the optional second parameter, an array of custom error messages to display
if the items are not valid. Internally, this uses the controller's
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

.. _controller-validatedata:

$this->validateData()
=====================

.. versionadded:: 4.2.0

Sometimes you may want to check the controller method parameters or other custom data.
In that case, you can use the ``$this->validateData()`` method.
The method accepts an array of data to validate in the first parameter:

.. literalinclude:: controllers/006.php

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

Since v4.2.0, the new more secure Auto Routing has been introduced.

.. note:: If you are familiar with Auto Routing, which was enabled by default
    from CodeIgniter 3 through 4.1.x, you can see the differences in
    :ref:`ChangeLog v4.2.0 <v420-new-improved-auto-routing>`.

This section describes the functionality of the new auto-routing.
It automatically routes an HTTP request, and executes the corresponding controller method
without route definitions.

Since v4.2.0, the auto-routing is disabled by default. To use it, see :ref:`enabled-auto-routing-improved`.

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

To specify a default controller open your **app/Config/Routes.php**
file and set this variable:

.. literalinclude:: controllers/015.php

Where ``Helloworld`` is the name of the controller class you want to be used.

A few lines further down **Routes.php** in the "Route Definitions" section, comment out the line:

.. literalinclude:: controllers/016.php

If you now browse to your site without specifying any URI segments you'll
see the "Hello World" message.

.. important:: When you use Auto Routing (Improved), you must remove the line
    ``$routes->get('/', 'Home::index');``. Because defined routes take
    precedence over Auto Routing, and controllers defined in the defined routes
    are denied access by Auto Routing (Improved) for security reasons.

For more information, please refer to the :ref:`routes-configuration-options` section of the
:ref:`URI Routing <routing-auto-routing-improved-configuration-options>` documentation.

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
your **app/Config/Routes.php** file.

CodeIgniter also permits you to map your URIs using its :ref:`Defined Route Routing <defined-route-routing>`..

.. _controller-auto-routing-legacy:

Auto Routing (Legacy)
*********************

This section describes the functionality of Auto Routing (Legacy) that is a routing system from CodeIgniter 3.
It automatically routes an HTTP request, and executes the corresponding controller method
without route definitions. The auto-routing is disabled by default.

.. warning:: To prevent misconfiguration and miscoding, we recommend that you do not use
    Auto Routing (Legacy). It is easy to create vulnerable apps where controller filters
    or CSRF protection are bypassed.

.. important:: Auto Routing (Legacy) routes a HTTP request with **any** HTTP method to a controller method.

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

To specify a default controller open your **app/Config/Routes.php**
file and set this variable:

.. literalinclude:: controllers/015.php

Where ``Helloworld`` is the name of the controller class you want to be used.

A few lines further down **Routes.php** in the "Route Definitions" section, comment out the line:

.. literalinclude:: controllers/016.php

If you now browse to your site without specifying any URI segments you'll
see the "Hello World" message.

.. note:: The line ``$routes->get('/', 'Home::index');`` is an optimization that you will want to use in a "real-world" app. But for demonstration purposes we don't want to use that feature. ``$routes->get()`` is explained in :doc:`URI Routing <routing>`

For more information, please refer to the :ref:`routes-configuration-options` section of the
:ref:`URI Routing <routing-auto-routing-legacy-configuration-options>` documentation.

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
your **app/Config/Routes.php** file.

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
