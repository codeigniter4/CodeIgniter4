#########
Factories
#########

.. contents::
    :local:
    :depth: 2

Introduction
************

What are Factories?
===================

Like :doc:`./services`, **Factories** are an extension of autoloading that helps keep your code
concise yet optimal, without having to pass around object instances between classes.

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

Example
=======

Take a look at **Models** as an example. You can access the Factory specific to Models
by using the magic static method of the Factories class, ``Factories::models()``.

By default, Factories first searches in the ``App`` namespace for the path corresponding to the magic static method name.
``Factories::models()`` searches the path **Models/**.

In the following code, if you have ``App\Models\UserModel``, the instance will be returned:

.. literalinclude:: factories/001.php

Or you could also request a specific class:

.. literalinclude:: factories/002.php

If you have only ``Blog\Models\UserModel``, the instance will be returned.
But if you have both ``App\Models\UserModel`` and ``Blog\Models\UserModel``,
the instance of ``App\Models\UserModel`` will be returned.

If you want to get ``Blog\Models\UserModel``, you need to disable the option ``preferApp``:

.. literalinclude:: factories/010.php

See :ref:`factories-options` for the details.

Next time you ask for the same class anywhere in your code, Factories will be sure
you get back the instance as before:

.. literalinclude:: factories/003.php

Convenience Functions
*********************

Two shortcut functions for Factories have been provided. These functions are always available.

config()
========

The first is ``config()`` which returns a new instance of a Config class. The only required parameter is the class name:

.. literalinclude:: factories/008.php

model()
=======

The second function, :php:func:`model()` returns a new instance of a Model class. The only required parameter is the class name:

.. literalinclude:: factories/009.php

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
path       string or null The relative path within the namespace/folder to look for    ``null`` (defaults to the component name)
                          classes.
instanceOf string or null A required class name to match on the returned instance.     ``null`` (no filtering)
getShared  boolean        Whether to return a shared instance of the class or load a   ``true``
                          fresh one.
preferApp  boolean        Whether a class with the same basename in the App namespace  ``true``
                          overrides other explicit class requests.
========== ============== ============================================================ ===================================================

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

For example, if you want to create **Filters** by Factories, the component name wll be ``filters``.
And if you want to ensure that each filter is an instance of a class which implements CodeIgniter's ``FilterInterface``,
your **app/Config/Factory.php** file might look like this:

.. literalinclude:: factories/005.php

Now you can create a filter with code like ``Factories::filters('SomeFilter')``,
and the returned instance will surely be a CodeIgniter's filter.

This would prevent conflict of an third-party module which happened to have an
unrelated ``Filters`` path in its namespace.

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
