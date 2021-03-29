#########
Factories
#########

.. contents::
    :local:
    :depth: 2

Introduction
============

Like ``Services``, ``Factories`` are an extension of autoloading that helps keep your code
concise yet optimal, without having to pass around object instances between classes. At its
simplest, Factories provide a common way to create a class instance and access it from
anywhere. This is a great way to reuse object states and reduce memory load from keeping
multiple instances loaded across your app.

Anything can be loaded by Factories, but the best examples are those classes that are used
to work on or transmit common data. The framework itself uses Factories internally, e.g., to
make sure the correct configuration is loaded when using the ``Config`` class. 

Take a look at ``Models`` as an example. You can access the Factory specific to ``Models``
by using the magic static method of the Factories class, ``Factories::models()``. Because of
the common path structure for namespaces and folders, Factories know that the model files
and classes are found within **Models**, so you can request a model by its shorthand base name::

    use CodeIgniter\Config\Factories;

    $users = Factories::models('UserModel');

Or you could also request a specific class::

    $widgets = Factories::models('Some\Namespace\Models\WidgetModel');

Next time you ask for the same class anywhere in your code, ``Factories`` will be sure
you get back the instance as before::

    class SomeOtherClass
    {
        $widgets = Factories::models('WidgetModel');
        // ...
    }

Factory Parameters
==================

``Factories`` takes as a second parameter an array of option values (described below).
These directives will override the default options configured for each component.

Any more parameters passed at the same time will be forwarded on to the class
constructor, making it easy to configure your class instance on-the-fly. For example, say
your app uses a separate database for authentication and you want to be sure that any attempts
to access user records always go through that connection::

    $conn  = db_connect('AuthDatabase');
    $users = Factories::models('UserModel', [], $conn);

Now any time the ``UserModel`` is loaded from ``Factories`` it will in fact be returning a
class instance that uses the alternate database connection.

Factories Options
==================

The default behavior might not work for every component. For example, say your component
name and its path do not align, or you need to limit instances to a certain type of class.
Each component takes a set of options to direct discovery and instantiation.

========== ============== ==================================================================================================================== ===================================================
Key        Type           Description                                                                                                          Default
========== ============== ==================================================================================================================== ===================================================
component  string or null The name of the component (if different than the static method). This can be used to alias one component to another. ``null`` (defaults to the component name)
path       string or null The relative path within the namespace/folder to look for classes.                                                   ``null`` (defaults to the component name)
instanceOf string or null A required class name to match on the returned instance.                                                             ``null`` (no filtering)
getShared  boolean        Whether to return a shared instance of the class or load a fresh one.                                                ``true``
preferApp  boolean        Whether a class with the same basename in the App namespace overrides other explicit class requests.                 ``true``
========== ============== ==================================================================================================================== ===================================================

Factories Behavior
==================

Options can be applied in one of three ways (listed in ascending priority):

* A configuration file ``Factory`` with a component property.
* The static method ``Factories::setOptions``.
* Passing options directly at call time with a parameter.

Configurations
--------------

To set default component options, create a new Config files at **app/Config/Factory.php**
that supplies options as an array property that matches the name of the component. For example,
if you wanted to ensure that all Filters used by your app were valid framework instances,
your **Factories.php** file might look like this::

    <?php

    namespace Config;

    use CodeIgniter\Config\Factory as BaseFactory;
    use CodeIgniter\Filters\FilterInterface;

    class Factories extends BaseFactory
    {
        public $filters = [
            'instanceOf' => FilterInterface::class,
        ];
    }

This would prevent conflict of an unrelated third-party module which happened to have an
unrelated "Filters" path in its namespace.

setOptions Method
-----------------

The ``Factories`` class has a static method to allow runtime option configuration: simply
supply the desired array of options using the ``setOptions()`` method and they will be
merged with the default values and stored for the next call::

    Factories::setOptions('filters', [
        'instanceOf' => FilterInterface::class,
        'prefersApp' => false,
    ]);

Parameter Options
-----------------

``Factories``'s magic static call takes as a second parameter an array of option values.
These directives will override the stored options configured for each component and can be
used at call time to get exactly what you need. The input should be an array with option
names as keys to each overriding value.

For example, by default ``Factories`` assumes that you want to locate a shared instance of
a component. By adding a second parameter to the magic static call, you can control whether
that single call will return a new or shared instance::

    $users = Factories::models('UserModel', ['getShared' => true]); // Default; will always be the same instance
    $other = Factories::models('UserModel', ['getShared' => false]); // Will always create a new instance
