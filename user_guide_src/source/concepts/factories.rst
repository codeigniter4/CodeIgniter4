#########
Factories
#########

.. contents::
    :local:
    :depth: 3

Introduction
************

What are Factories?
===================

Like :doc:`./services`, **Factories** are an extension of autoloading that helps keep your code
concise yet optimal, without having to pass around object instances between classes.

Factories are similar to CodeIgniter 3's ``$this->load`` in the following points:

- Load a class
- Share the loaded class instance

At its
simplest, Factories provide a common way to create a class instance and access it from
anywhere. This is a great way to reuse object states and reduce memory load from keeping
multiple instances loaded across your app.

Any class can be loaded by Factories, but the best examples are those classes that are used
to work on or transmit common data. The framework itself uses Factories internally, e.g., to
make sure the correct configuration is loaded when using the ``Config`` class.

Differences from Services
=========================

Factories require a concrete class name to instantiate and do not have code to create instances.

So, Factories are not good for creating a complex instance that needs many dependencies,
and you cannot change the class of the instance to be returned.

On the other hand, Services have code to create instances, so it can create a complex instance
that needs other services or class instances. When you get a service, Services require a service name,
not a class name, so the returned instance can be changed without changing the client code.

.. _factories-loading-class:

Loading Classes
***************

Loading a Class
===============

Take a look at **Models** as an example. You can access the Factory specific to Models
by using the magic static method of the Factories class, ``Factories::models()``.

The static method name is called *component*.

.. _factories-passing-classname-without-namespace:

Passing Classname without Namespace
-----------------------------------

If you pass a classname without a namespace, Factories first searches in the
``App`` namespace for the path corresponding to the magic static method name.
``Factories::models()`` searches the **app/Models** directory.

Passing Short Classname
^^^^^^^^^^^^^^^^^^^^^^^

In the following code, if you have ``App\Models\UserModel``, the instance will be returned:

.. literalinclude:: factories/001.php

If you don't have ``App\Models\UserModel``, it searches for ``Models\UserModel`` in all namespaces.

Next time you ask for the same class anywhere in your code, Factories will be sure
you get back the instance as before:

.. literalinclude:: factories/003.php

Passing Short Classname with Sub-directories
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If you want to load a class in sub directories, you use the ``/`` as a separator.
The following code loads **app/Libraries/Sub/SubLib.php** if it exists:

.. literalinclude:: factories/013.php
   :lines: 2-

Passing Full Qualified Classname
--------------------------------

You could also request a full qualified classname:

.. literalinclude:: factories/002.php
   :lines: 2-

It returns the instance of ``Blog\Models\UserModel`` if it exists.

.. note:: Prior to v4.4.0, when you requested a full qualified classname,
    if you had only ``Blog\Models\UserModel``, the instance would be returned.
    But if you had both ``App\Models\UserModel`` and ``Blog\Models\UserModel``,
    the instance of ``App\Models\UserModel`` would be returned.

    If you wanted to get ``Blog\Models\UserModel``, you needed to disable the
    option ``preferApp``:

    .. literalinclude:: factories/010.php
       :lines: 2-

Convenience Functions
*********************

Two shortcut functions for Factories have been provided. These functions are always available.

.. _factories-config:

config()
========

The first is :php:func:`config()` which returns a new instance of a Config class. The only required parameter is the class name:

.. literalinclude:: factories/008.php

model()
=======

The second function, :php:func:`model()` returns a new instance of a Model class. The only required parameter is the class name:

.. literalinclude:: factories/009.php

.. _factories-defining-classname-to-be-loaded:

Defining Classname to be Loaded
*******************************

.. versionadded:: 4.4.0

You could define a classname to be loaded before loading the class with
the ``Factories::define()`` method:

.. literalinclude:: factories/014.php
   :lines: 2-

The first parameter is a component. The second parameter is a class alias
(the first parameter to Factories magic static method), and the third parameter
is the true full qualified classname to be loaded.

After that, if you load ``Myth\Auth\Models\UserModel`` with Factories, the
``App\Models\UserModel`` instance will be returned:

.. literalinclude:: factories/015.php
   :lines: 2-

Factory Parameters
******************

``Factories`` takes as a second parameter an array of option values (described below).
These directives will override the default options configured for each component.

Any more parameters passed at the same time will be forwarded on to the class
constructor, making it easy to configure your class instance on-the-fly. For example, say
your app uses a separate database for authentication and you want to be sure that any attempts
to access user records always go through that connection:

.. literalinclude:: factories/004.php

Now any time the ``UserModel`` is loaded from ``Factories`` it will in fact be returning a
class instance that uses the alternate database connection.

.. _factories-options:

Factories Options
*****************

The default behavior might not work for every component. For example, say your component
name and its path do not align, or you need to limit instances to a certain type of class.
Each component takes a set of options to direct discovery and instantiation.

========== ============== ============================================================ ===================================================
Key        Type           Description                                                  Default
========== ============== ============================================================ ===================================================
component  string or null The name of the component (if different than the static      ``null`` (defaults to the component name)
                          method). This can be used to alias one component to another.
path       string or null The relative path within the namespace/folder to look for    ``null`` (defaults to the component name,
                          classes.                                                     but makes the first character uppercase)
instanceOf string or null A required class name to match on the returned instance.     ``null`` (no filtering)
getShared  boolean        Whether to return a shared instance of the class or load a   ``true``
                          fresh one.
preferApp  boolean        Whether a class with the same basename in the App namespace  ``true``
                          overrides other explicit class requests.
========== ============== ============================================================ ===================================================

.. note:: Since v4.4.0, ``preferApp`` works only when you request
    :ref:`a classname without a namespace <factories-passing-classname-without-namespace>`.

Factories Behavior
******************

Options can be applied in one of three ways (listed in ascending priority):

* A configuration class ``Config\Factory`` with a property that matches the name of a component.
* The static method ``Factories::setOptions()``.
* Passing options directly at call time with a parameter.

Configurations
==============

To set default component options, create a new Config files at **app/Config/Factory.php**
that supplies options as an array property that matches the name of the component.

Example: Filters Factories
--------------------------

For example, if you want to create **Filters** by Factories, the component name wll be ``filters``.
And if you want to ensure that each filter is an instance of a class which implements CodeIgniter's ``FilterInterface``,
your **app/Config/Factory.php** file might look like this:

.. literalinclude:: factories/005.php

Now you can create a filter with code like ``Factories::filters('SomeFilter')``,
and the returned instance will surely be a CodeIgniter's filter.

This would prevent conflict of an third-party module which happened to have an
unrelated ``Filters`` path in its namespace.

Example: Library Factories
--------------------------

If you want to load your library classes in the **app/Libraries** directory with
``Factories::library('SomeLib')``, the path `Libraries` is different from the
default path `Library`.

In this case, your **app/Config/Factory.php** file will look like this:

.. literalinclude:: factories/011.php

Now you can load your libraries with the ``Factories::library()`` method:

.. literalinclude:: factories/012.php
   :lines: 2-

setOptions Method
=================

The ``Factories`` class has a static method to allow runtime option configuration: simply
supply the desired array of options using the ``setOptions()`` method and they will be
merged with the default values and stored for the next call:

.. literalinclude:: factories/006.php

Parameter Options
=================

``Factories``'s magic static call takes as a second parameter an array of option values.
These directives will override the stored options configured for each component and can be
used at call time to get exactly what you need. The input should be an array with option
names as keys to each overriding value.

For example, by default ``Factories`` assumes that you want to locate a shared instance of
a component. By adding a second parameter to the magic static call, you can control whether
that single call will return a new or shared instance:

.. literalinclude:: factories/007.php
   :lines: 2-

.. _factories-config-caching:

Config Caching
**************

.. versionadded:: 4.4.0

To improve performance, Config Caching has been implemented.

Prerequisite
============

.. important:: Using this feature when the prerequisites are not met will prevent
    CodeIgniter from operating properly. Do not use this feature in such cases.

- To use this feature, the properties of all Config objects instantiated in
  Factories must not be modified after instantiation. Put another way, the Config
  classes must be an immutable or readonly classes.
- By default, every Config class that is cached must implement ``__set_state()``
  method.

How It Works
============

- Save the all Config instances in Factories into a cache file before shutdown,
  if the state of the Config instances in Factories changes.
- Restore cached Config instances before CodeIgniter initialization if a cache
  is available.

Simply put, all Config instances held by Factories are cached immediately prior
to shutdown, and the cached instances are used permanently.

How to Update Config Values
===========================

Once stored, the cached versions never expire. Changing a existing Config file
(or changing Environment Variables for it) will not update the cache nor the Config
values.

So if you want to update Config values, update Config files or Environment Variables
for them, and you must manually delete the cache file.

You can use the ``spark cache:clear`` command:

.. code-block:: console

    php spark cache:clear

Or simply delete the **writable/cache/FactoriesCache_config** file.

How to Enable Config Caching
============================

Uncomment the following code in **public/index.php**::

    --- a/public/index.php
    +++ b/public/index.php
    @@ -49,8 +49,8 @@ if (! defined('ENVIRONMENT')) {
     }

     // Load Config Cache
    -// $factoriesCache = new \CodeIgniter\Cache\FactoriesCache();
    -// $factoriesCache->load('config');
    +$factoriesCache = new \CodeIgniter\Cache\FactoriesCache();
    +$factoriesCache->load('config');
     // ^^^ Uncomment these lines if you want to use Config Caching.

     /*
    @@ -79,7 +79,7 @@ $app->setContext($context);
     $app->run();

     // Save Config Cache
    -// $factoriesCache->save('config');
    +$factoriesCache->save('config');
     // ^^^ Uncomment this line if you want to use Config Caching.

     // Exits the application, setting the exit code for CLI-based applications
