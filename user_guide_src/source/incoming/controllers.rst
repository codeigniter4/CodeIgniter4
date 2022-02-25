###########
Controllers
###########

Controllers are the heart of your application, as they determine how HTTP requests should be handled.

.. contents::
    :local:
    :depth: 2

What is a Controller?
=====================

A Controller is simply a class file that is named in a way that it can be associated with a URI.

Consider this URI::

    example.com/index.php/helloworld/

In the above example, CodeIgniter would attempt to find a controller named **Helloworld.php** and load it.

**When a controller's name matches the first segment of a URI, it will be loaded.**

Let's try it: Hello World!
==========================

Let's create a simple controller so you can see it in action. Using your text editor, create a file called **Helloworld.php**,
and put the following code in it. You will notice that the ``Helloworld`` Controller is extending the ``BaseController``. you can
also extend the ``CodeIgniter\Controller`` if you do not need the functionality of the BaseController.

The BaseController provides a convenient place for loading components and performing functions that are needed by all your
controllers. You can extend this class in any new controller.

For security reasons be sure to declare any new utility methods as ``protected`` or ``private``:

.. literalinclude:: controllers/001.php

Then save the file to your **/app/Controllers/** directory.

.. important:: The file must be called **Helloworld.php**, with a capital ``H``.

Now visit your site using a URL similar to this::

    example.com/index.php/helloworld

If you did it right you should see::

    Hello World!

.. important:: Controller class names MUST start with an uppercase letter and ONLY the first character can be uppercase.

This is valid:

.. literalinclude:: controllers/002.php

This is **not** valid:

.. literalinclude:: controllers/003.php

This is **not** valid:

.. literalinclude:: controllers/004.php

Also, always make sure your controller extends the parent controller
class so that it can inherit all its methods.

.. note::
    The system will attempt to match the URI against Controllers by matching each segment against
    folders/files in **app/Controllers/**, when a match wasn't found against defined routes.
    That's why your folders/files MUST start with a capital letter and the rest MUST be lowercase.
    If you want another naming convention you need to manually define it using the
    :doc:`URI Routing <routing>` feature.

    Here is an example based on PSR-4 Autoloader:

    .. literalinclude:: controllers/005.php

Methods
=======

In the above example, the method name is ``index()``. The ``index()`` method
is always loaded by default if the **second segment** of the URI is
empty. Another way to show your "Hello World" message would be this::

    example.com/index.php/helloworld/index/

**The second segment of the URI determines which method in the
controller gets called.**

Let's try it. Add a new method to your controller:

.. literalinclude:: controllers/006.php

Now load the following URL to see the comment method::

    example.com/index.php/helloworld/comment/

You should see your new message.

Passing URI Segments to your methods
====================================

If your URI contains more than two segments they will be passed to your
method as parameters.

For example, let's say you have a URI like this::

    example.com/index.php/products/shoes/sandals/123

Your method will be passed URI segments 3 and 4 (``'sandals'`` and ``'123'``):

.. literalinclude:: controllers/007.php

.. important:: If you are using the :doc:`URI Routing <routing>`
    feature, the segments passed to your method will be the re-routed
    ones.

Defining a Default Controller
=============================

CodeIgniter can be told to load a default controller when a URI is not
present, as will be the case when only your site root URL is requested. Let's try it
with the ``Helloworld`` controller.

To specify a default controller open your **app/Config/Routes.php**
file and set this variable:

.. literalinclude:: controllers/008.php

Where ``Helloworld`` is the name of the controller class you want to be used.

A few lines further down **Routes.php** in the "Route Definitions" section, comment out the line:

.. literalinclude:: controllers/009.php

If you now browse to your site without specifying any URI segments you'll
see the "Hello World" message.

.. note:: The line ``$routes->get('/', 'Home::index');`` is an optimization that you will want to use in a "real-world" app. But for demonstration purposes we don't want to use that feature. ``$routes->get()`` is explained in :doc:`URI Routing <routing>`

For more information, please refer to the :ref:`routes-configuration-options` section of the
:doc:`URI Routing <routing>` documentation.

Remapping Method Calls
======================

As noted above, the second segment of the URI typically determines which
method in the controller gets called. CodeIgniter permits you to override
this behavior through the use of the ``_remap()`` method:

.. literalinclude:: controllers/010.php

.. important:: If your controller contains a method named ``_remap()``,
    it will **always** get called regardless of what your URI contains. It
    overrides the normal behavior in which the URI determines which method
    is called, allowing you to define your own method routing rules.

The overridden method call (typically the second segment of the URI) will
be passed as a parameter to the ``_remap()`` method:

.. literalinclude:: controllers/011.php

Any extra segments after the method name are passed into ``_remap()``. These parameters can be passed to the method
to emulate CodeIgniter's default behavior.

Example:

.. literalinclude:: controllers/012.php

Private methods
===============

In some cases, you may want certain methods hidden from public access.
To achieve this, simply declare the method as ``private`` or ``protected``.
That will prevent it from being served by a URL request. For example,
if you were to define a method like this for the ``Helloworld`` controller:

.. literalinclude:: controllers/013.php

then trying to access it using the following URL will not work::

    example.com/index.php/helloworld/utility/

Organizing Your Controllers into Sub-directories
================================================

If you are building a large application you might want to hierarchically
organize or structure your controllers into sub-directories. CodeIgniter
permits you to do this.

Simply create sub-directories under the main **app/Controllers/**,
and place your controller classes within them.

.. important:: Folder names MUST start with an uppercase letter and ONLY the first character can be uppercase.

When using this feature the first segment of your URI must
specify the folder. For example, let's say you have a controller located here::

    app/Controllers/Products/Shoes.php

To call the above controller your URI will look something like this::

    example.com/index.php/products/shoes/show/123

.. note:: You cannot have directories with the same name in **app/Controllers/** and **public/**.
    This is because if there is a directory, the web server will search for it and
    it will not be routed to CodeIgniter.

Each of your sub-directories may contain a default controller which will be
called if the URL contains *only* the sub-directory. Simply put a controller
in there that matches the name of your default controller as specified in
your **app/Config/Routes.php** file.

CodeIgniter also permits you to remap your URIs using its :doc:`URI Routing <routing>` feature.

Included Properties
===================

Every controller you create should extend ``CodeIgniter\Controller`` class.
This class provides several features that are available to all of your controllers.

**Request Object**

The application's main :doc:`Request Instance </incoming/request>` is always available
as a class property, ``$this->request``.

**Response Object**

The application's main :doc:`Response Instance </outgoing/response>` is always available
as a class property, ``$this->response``.

**Logger Object**

An instance of the :doc:`Logger <../general/logging>` class is available as a class property,
``$this->logger``.

**forceHTTPS**

A convenience method for forcing a method to be accessed via HTTPS is available within all
controllers:

.. literalinclude:: controllers/014.php

By default, and in modern browsers that support the HTTP Strict Transport Security header, this
call should force the browser to convert non-HTTPS calls to HTTPS calls for one year. You can
modify this by passing the duration (in seconds) as the first parameter:

.. literalinclude:: controllers/015.php

.. note:: A number of :doc:`time-based constants </general/common_functions>` are always available for you to use, including ``YEAR``, ``MONTH``, and more.

Helpers
-------

You can define an array of helper files as a class property. Whenever the controller is loaded
these helper files will be automatically loaded into memory so that you can use their methods anywhere
inside the controller:

.. literalinclude:: controllers/016.php

.. _controllers-validating-data:

Validating data
===============

$this->validate()
-----------------

To simplify data checking, the controller also provides the convenience method ``validate()``.
The method accepts an array of rules in the first parameter,
and in the optional second parameter, an array of custom error messages to display
if the items are not valid. Internally, this uses the controller's
``$this->request`` instance to get the data to be validated.
The :doc:`Validation Library docs </libraries/validation>` have details on
rule and message array formats, as well as available rules:

.. literalinclude:: controllers/017.php

If you find it simpler to keep the rules in the configuration file, you can replace
the ``$rules`` array with the name of the group as defined in ``Config\Validation.php``:

.. literalinclude:: controllers/018.php

.. note:: Validation can also be handled automatically in the model, but sometimes it's easier to do it in the controller. Where is up to you.

$this->validateData()
---------------------

Sometimes you may want to check the controller method parameters or other custom data.
In that case, you can use the ``$this->validateData()`` method.
The method accepts an array of data to validate in the first parameter:

.. literalinclude:: controllers/019.php

That's it!
==========

That, in a nutshell, is all there is to know about controllers.
