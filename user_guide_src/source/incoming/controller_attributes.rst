.. _incoming/controller_attributes:

#####################
Controller Attributes
#####################

PHP Attributes can be used to define filters and other metadata on controller classes and methods. This keeps the configuration close to the code it affects, and can make it easier to see at a glance what filters are applied to a given controller or method. This works across all routing methods, including auto-routing, which allows for a near feature-parity between the more robust route declarations and auto-routing.

.. contents::
    :local:
    :depth: 2

Getting Started
***************

Controller Attributes can be applied to either the entire class, or to a specific method. The following example shows how to apply the ``Filters`` attribute to a controller class:

.. literalinclude:: controller_attributes/001.php

In this example, the ``Auth`` filter will be applied to all methods in ``AdminController``.

You can also apply the ``Filters`` attribute to a specific method within a controller. This allows you to apply filters only to certain methods, while leaving others unaffected. Here's an example:

.. literalinclude:: controller_attributes/002.php

Class-level and method-level attributes can work together to provide a flexible way to manage your routes at the controller level.

Disabling Attributes
--------------------

If you know that you will not be using attributes in your application, you can disable the feature by setting the ``$useControllerAttributes`` property in your ``app/Config/Routing.php`` file to ``false``.

Provided Attributes
*******************

Filter
-------

The ``Filters`` attribute allows you to specify one or more filters to be applied to a controller class or method. You can specify filters to run before or after the controller action, and you can also provide parameters to the filters. Here's an example of how to use the ``Filters`` attribute:

.. literalinclude:: controller_attributes/003.php

.. note::

    When filters are applied both by an attribute and in the filter configuration file, they will both be applied, but that could lead to unexpected results.

.. note::

    Please remember that every parameter applied to the filter will be converted to a string. This behavior affects only filters.

Restrict
--------

The ``Restrict`` attribute allows you to restrict access to the class or method based on the domain, the sub-domain, or
the environment the application is running in. Here's an example of how to use the ``Restrict`` attribute:

.. literalinclude:: controller_attributes/004.php

Cache
-----

The ``Cache`` attribute allows the output of the controller method to be cached for a specified amount of time. You can specify a duration in seconds, and optionally a cache key. Here's an example of how to use the ``Cache`` attribute:

.. literalinclude:: controller_attributes/005.php

Custom Attributes
*****************

You can also create your own custom attributes to add metadata or behavior to your controllers and methods. Custom attributes must implement the ``CodeIgniter\Router\Attributes\RouteAttributeInterface`` interface. Here's an example of a custom attribute that adds a custom header to the response:

.. literalinclude:: controller_attributes/006.php

You can then apply this custom attribute to a controller class or method just like the built-in attributes:

.. literalinclude:: controller_attributes/007.php
