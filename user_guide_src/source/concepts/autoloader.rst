#################
Autoloading Files
#################

Every application consists of a large number of classes in many different locations.
The framework provides classes for core functionality. Your application will have a
number of libraries, models, and other entities to make it work. You might have third-party
classes that your project is using. Keeping track of where every single file is, and
hard-coding that location into your files in a series of ``requires()`` is a massive
headache and very error-prone. That's where autoloaders come in.

CodeIgniter provides a very flexible autoloader that can be used with very little configuration.
It can locate individual non-namespaced classes, namespaced classes that adhere to PSR4 autoloading
directory structures, and will even attempt to locate classes in legacy directories to ease the
transition from previous versions.

The autoloader works great by itself, but can also work with other autoloaders, like Composer,
or even your own custom autoloaders, if needed. Because they're all registered through
`spl_autoload_register <http://php.net/manual/en/function.spl-autoload-register.php>`_,
they work in sequence and don't get in each other's way.

The autoloader is always active, being registered with ``spl_autoload_register()`` at the
beginning of the framework's execution.

Configuration
=============

Initial configuration is done in ``/application/config/autoload.php``. This file contains two primary
arrays: one for the classmap, and one for PSR4-compatible namespaces.

Namespaces
==========

The recommended method for organizing your classes is to create one of more namespaces for your
application's files. This is most important for any business-logic related classes, entity classes,
etc. The ``psr4`` array in the configuration file allows you to map the namespace to the directory
those classes can be found in::

	$config['psr4'] = [
		'App'         => APPPATH,
		'CodeIgniter' => BASEPATH,
	];

The key of each row is the namespace itself. This does not need a trailing slash. If you use double-quotes
to define the array, be sure to escape the backwards slash. That means that it would be ``My\\App``,
not ``My\App``. The value is the location to the directory the classes can be found in. They should
have a trailing slash.

By default, the application folder is namespace to the ``App`` namespace. The only place this is used
in a fresh installation is in the configuration files. While you are not forced to namespace the controllers,
libraries, or models in the application directory, if you do, they will be found under the ``App`` namespace.
You may change this namespace by editing the ``/application/config/Constants.php`` file and setting the
new namespace value under the ``APP_NAMESPACE`` setting.::

	define('APP_NAMESPACE', 'App');

You will need to modify any existing files that are referencing the current namespace. In a fresh install,
those are found in the config directory.

Classmap
========

The classmap is used extensively by CodeIgniter to eek the last ounces of performance out of the system
by not hitting the file-system with extra ``file_exists()`` calls. You can use the classmap to link to
third-party libraries that are not namespaced::

	$config['classmap'] = [
		'Markdown' => APPPATH .'third_party/markdown.php'
	];

The key of each row is the name of the class that you want to locate. The value is the path to locate it at.

Legacy Support
==============

If neither of the above methods find the class, and the class is not namespaced, the autoloader will look in the
``/application/libraries`` and ``/application/models`` directories to attempt to locate the files. This provides
a measure to help ease the transition from previous versions.

There are no configuration options for legacy support.
