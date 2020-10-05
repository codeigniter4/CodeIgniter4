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
simplest, ``Factories`` provides a common way to create a class instance and access it from
anywhere. This is a great way to reuse object states and reduce memory load from keeping
multiple instances loaded across your app.

Anything can be loaded from a Factory, but the best examples are those classes that are used
to work on or transmit common data. The framework itself makes heavy use of the ``Config``
Factory to make sure the correct configuration is in use at any time. 

Take a look at ``Models`` as an example. You can access the Factory specific to ``Models``
by using the magic static method of the Factories class, ``Factories::models()``. Because
of the common path structure for namespaces and folders, ``Factories`` knows to look for
files and classes within ``Models``, so you can request a model by its shorthand base name::

	use CodeIgniter\Config\Factories;

	$users = Factories::models('UserModel');

Or you could also request a specific class::

	$widgets = Factories::models('Some\Namespace\Models\WidgetModel');

Next time you ask for the same class anywhere in your code, ``Factories`` will be sure
you get back the instance as before::

	class SomeOtherClass
	{
		$widgets = Factories::models('WidgetModel');


Factories Behavior
==================

The default behavior of ``Factories`` might not always work for ever component. For example,
say your component name and its path do not align, or you need to be sure that only a certain
type of class instances are returned, or your component classes require additional parameters
for their constructor. Learn how to use ``Factories`` and its configuration to meet your needs.

Factory Parameters
------------------

By default, ``Factories`` assumes that you want to locate a shared instance of a component
with the provided name. The convenience of ``Factories`` for locating classes is sometimes
desirable without using its shared instance storage. By adding a second parameter to the
magic static call, you can control whether ``Factories`` will return a new or shared instance
each time::

	$users = Factories::models('UserModel', true); // Default; will always be the same instance
	$other = Factories::models('UserModel', false); // Will always create a new instance

Additionally, any more parameters passed at the same time will be forwarded on to the class
constructor, making it easy to configure your shared instance on-the-fly. For example, say
your app uses a separate database for authentication and you want to be sure that any attempts
to access user records always go through that connection::

	$conn  = db_connect('AuthDatabase');
	$users = Factories::models('UserModel', true, $conn);

Now any time the ``UserModel`` is loaded from ``Factories`` it will in fact be returning the
shared instance that uses the alternate database connection.

Factory Configuration
---------------------

What works for one component may not work for all. ``Factories`` supports alternate behavior
at the component level via configurations. A configuration consists of any of the settings,
which will fall back on the default if not provided.

======================= ============== ==================================================================================================================== ===================================================
Key                     Type           Description                                                                                     Default
======================= ============== ==================================================================================================================== ===================================================
component               string or null The name of the component (if different than the static method). This can be used to alias one component to another. ``null`` (defaults to the component name)
path                    string or null The relative path within the namespace/folder to look for classes.                                                   ``null`` (defaults to the component name)
instanceOf              string or null A required class name to match on the returned instance.                                                             ``null`` (no filtering)
prefersApp              boolean        Whether a class with the same basename in the App namespace overrides other explicit class requests.                 ``true``

Configurations can be applied in one of two ways: through a configuration file or at runtime.
To use the file, create a new configuration at **app/Config/Factory.php** that supplies
configuration settings in a property matching the configuration name. For example, if you
wanted to ensure that all Filters used by your app were really valid your **Factories.php**
file might look like this::

	<?php namespace Config;

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

Runtime configuration is even easier: simply supply the desired configuration values to
``Factories`` using the ``setConfig()`` method and they will be merged with the default
values and stored for the next call::

	Factories::setConfig('filters', [
		'instanceOf' => FilterInterface::class,
		'prefersApp' => false,
	]);
