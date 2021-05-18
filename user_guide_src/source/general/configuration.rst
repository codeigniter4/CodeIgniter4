#############
Configuration
#############

Every framework uses configuration files to define numerous parameters and
initial settings. CodeIgniter configuration files define simple classes where
the required settings are public properties.

Unlike many other frameworks, CodeIgniter configurable items aren't contained in
a single file. Instead, each class that needs configurable items will have a
configuration file with the same name as the class that uses it. You will find
the application configuration files in the **/app/Config** folder.

.. contents::
    :local:
    :depth: 2

Working With Configuration Files
================================

You can access configuration files for your classes in several different ways.

- By using the ``new`` keyword to create an instance::

    // Creating new configuration object by hand
    $config = new \Config\Pager();

- By using the ``config()`` function::

    // Get shared instance with config function
    $config = config('Pager');

    // Access config class with namespace
    $config = config( 'Config\\Pager' );

    // Creating a new object with config function
    $config = config('Pager', false);

All configuration object properties are public, so you access the settings like any other property::

    $config = config('Pager');
    // Access settings as object properties
    $pageSize = $config->perPage;

If no namespace is provided, it will look for the file in all defined namespaces
as well as **/app/Config/**.

All of the configuration files that ship with CodeIgniter are namespaced with
``Config``. Using this namespace in your application will provide the best
performance since it knows exactly where to find the files.

You can put configuration files in any folder you want by using a different namespace.
This allows you to put configuration files on the production server in a folder
that is not web-accessible while keeping it under **/app** for easy access
during development.

Creating Configuration Files
============================

When you need a new configuration, first you create a new file at your desired location.
The default file location (recommended for most cases) is **/app/Config**.
The class should use the appropriate namespace, and it should extend
``CodeIgniter\Config\BaseConfig`` to ensure that it can receive environment-specific settings.

Define the class and fill it with public properties that represent your settings.::

    <?php

    namespace Config;

    use CodeIgniter\Config\BaseConfig;

    class CustomClass extends BaseConfig
    {
        public $siteName  = 'My Great Site';
        public $siteEmail = 'webmaster@example.com';

    }

Environment Variables
=====================

One of today’s best practices for application setup is to use Environment Variables. One reason for this is that Environment Variables are easy to change between deploys without changing any code. Configuration can change a lot across deploys, but code does not. For instance, multiple environments, such as the developer’s local machine and the production server, usually need different configuration values for each particular setup.

Environment Variables should also be used for anything private such as passwords, API keys, or other sensitive data.

Environment Variables and CodeIgniter
=====================================

CodeIgniter makes it simple and painless to set Environment Variables by using a “dotenv” file. The term comes from the file name, which starts with a dot before the text “env”.

CodeIgniter expects **.env** to be at the root of your project alongside the ``system``
and ``app`` directories. There is a template file distributed with CodeIgniter that’s
located at the project root named **env** (Notice there’s no dot (**.**) at the start?).
It has a large collection of variables your project might use that have been assigned
empty, dummy, or default values. You can use this file as a starting place for your
application by either renaming the template to **.env**, or by making a copy of it named **.env**.

.. important:: Make sure the **.env** file is NOT tracked by your version control system. For *git* that means adding it to **.gitignore**. Failure to do so could result in sensitive credentials being exposed to the public.

Settings are stored in **.env** files as a simple a collection of name/value pairs separated by an equal sign.
::

    S3_BUCKET = dotenv
    SECRET_KEY = super_secret_key
    CI_ENVIRONMENT = development

When your application runs, **.env** will be loaded automatically, and the variables put
into the environment. If a variable already exists in the environment, it will NOT be
overwritten. The loaded Environment variables are accessed using any of the following:
``getenv()``, ``$_SERVER``, or ``$_ENV``.
::

    $s3_bucket = getenv('S3_BUCKET');
    $s3_bucket = $_ENV['S3_BUCKET'];
    $s3_bucket = $_SERVER['S3_BUCKET'];

.. important:: Note that your settings from the **.env** file are added to Environment Variables. As a side effect, this means that if your CodeIgniter application is (for example) generating a ``var_dump($_ENV)`` or ``phpinfo()`` (for debugging or other valid reasons) **your secure credentials are publicly exposed**.

Nesting Variables
=================

To save on typing, you can reuse variables that you've already specified in the file by wrapping the
variable name within ``${...}``
::

        BASE_DIR="/var/webroot/project-root"
        CACHE_DIR="${BASE_DIR}/cache"
        TMP_DIR="${BASE_DIR}/tmp"

Namespaced Variables
====================

There will be times when you will have several variables with the same name.
The system needs a way of knowing what the correct setting should be.
This problem is solved by "*namespacing*" the variables.

Namespaced variables use a dot notation to qualify variable names so they will be unique
when incorporated into the environment. This is done by including a distinguishing
prefix followed by a dot (.), and then the variable name itself.
::

    // not namespaced variables
    name = "George"
    db=my_db

    // namespaced variables
    address.city = "Berlin"
    address.country = "Germany"
    frontend.db = sales
    backend.db = admin
    BackEnd.db = admin

Configuration Classes and Environment Variables
===============================================

When you instantiate a configuration class, any *namespaced* environment variables
are considered for merging into the configuration object's properties.

If the prefix of a namespaced variable exactly matches the namespace of the configuration
class, then the trailing part of the setting (after the dot) is treated as a configuration
property. If it matches an existing configuration property, the environment variable's
value will replace the corresponding value from the configuration file. If there is no match,
the configuration class properties are left unchanged. In this usage, the prefix must be
the full (case-sensitive) namespace of the class.
::

    Config\App.CSRFProtection = true
    Config\App.CSRFCookieName = csrf_cookie
    Config\App.CSPEnabled = true


.. note:: Both the namespace prefix and the property name are case-sensitive. They must exactly match the full namespace and property names as defined in the configuration class file.

The same holds for a *short prefix*, which is a namespace using only the lowercase version of
the configuration class name. If the short prefix matches the class name,
the value from **.env** replaces the configuration file value.
::

    app.CSRFProtection = true
    app.CSRFCookieName = csrf_cookie
    app.CSPEnabled = true

.. note:: When using the *short prefix* the property names must still exactly match the class defined name.

Environment Variables as Replacements for Data
==============================================

It is very important to always remember that environment variables contained in your **.env** are
**only replacements for existing data**. This means that you cannot expect to fill your ``.env`` with all
the replacements for your configurations but have nothing to receive these replacements in the
related configuration file(s).

The ``.env`` only serves to fill or replace the values in your configuration files. That said, your
configuration files should have a container or receiving property for those. Adding so many variables in
your ``.env`` with nothing to contain them in the receiving end is useless.

Simply put, you cannot just put ``app.myNewConfig = foo`` in your ``.env`` and expect your ``Config\App``
to magically have that property and value at run time.

Treating Environment Variables as Arrays
========================================

A namespaced environment variable can be further treated as an array.
If the prefix matches the configuration class, then the remainder of the
environment variable name is treated as an array reference if it also
contains a dot.
::

    // regular namespaced variable
    Config\SimpleConfig.name = George

    // array namespaced variables
    Config\SimpleConfig.address.city = "Berlin"
    Config\SimpleConfig.address.country = "Germany"

If this was referring to a SimpleConfig configuration object, the above example would be treated as::

    $address['city']    = "Berlin";
    $address['country'] = "Germany";

Any other elements of the ``$address`` property would be unchanged.

You can also use the array property name as a prefix. If the environment file
held the following then the result would be the same as above.
::

    // array namespaced variables
    Config\SimpleConfig.address.city = "Berlin"
    address.country = "Germany"


Handling Different Environments
===============================

Configuring multiple environments is easily accomplished by using a separate **.env** file with values modified to meet that environment's needs.

The file should not contain every possible setting for every configuration class used by the application. In truth, it should include only those items that are specific to the environment or are sensitive details like passwords and API keys and other information that should not be exposed. But anything that changes between deployments is fair-game.

In each environment, place the **.env** file in the project's root folder. For most setups, this will be the same level as the ``system`` and ``app`` directories.

Do not track **.env** files with your version control system. If you do, and the repository is made public, you will have put sensitive information where everybody can find it.

.. _registrars:

Registrars
==========

"Registrars" are any other classes which might provide additional configuration properties.
Registrars provide a means of altering a configuration at runtime across namespaces and files.
There are two ways to implement a Registrar: implicit and explicit.

.. note:: Values from **.env** always take priority over Registrars.

Implicit Registrars
-------------------

Any namespace may define registrars by using the **Config/Registrar.php** file, if discovery
is enabled in :doc:`Modules </general/modules>`. These files are classes whose methods are
named for each configuration class you wish to extend. For example, a third-party module might
wish to supply an additional template to ``Pager`` without overwriting whatever a develop has
already configured. In **src/Config/Registrar.php** there would be a ``Registrar`` class with
the single ``Pager()`` method (note the case-sensitivity)::

	class Registrar
	{
		public static function Pager(): array
		{
			return [
				'templates' => [
					'module_pager' => 'MyModule\Views\Pager',
				],
			];
		}
	}

Registrar methods must always return an array, with keys corresponding to the properties
of the target config file. Existing values are merged, and Registrar properties have
overwrite priority.

Explicit Registrars
-------------------

A configuration file can also specify any number of registrars explicitly.
This is done by adding a ``$registrars`` property to your configuration file,
holding an array of the names of candidate registrars.::

    public static $registrars = [
        SupportingPackageRegistrar::class
    ];

In order to act as a "registrar" the classes so identified must have a
static function with the same name as the configuration class, and it should return an associative
array of property settings.

When your configuration object is instantiated, it will loop over the
designated classes in ``$registrars``. For each of these classes it will invoke
the method named for the configuration class and incorporate any returned properties.

A sample configuration class setup for this::

    <?php

    namespace App\Config;

    use CodeIgniter\Config\BaseConfig;

    class MySalesConfig extends BaseConfig
    {
        public $target            = 100;
        public $campaign          = "Winter Wonderland";
        public static $registrars = [
            '\App\Models\RegionalSales'
        ];
    }

... and the associated regional sales model might look like::

    <?php

    namespace App\Models;

    class RegionalSales
    {
        public static function MySalesConfig()
        {
            return [
                'target' => 45,
                'actual' => 72,
            ];
        }
    }

With the above example, when ``MySalesConfig`` is instantiated, it will end up with
the two properties declared, but the value of the ``$target`` property will be overridden
by treating ``RegionalSales`` as a "registrar". The resulting configuration properties::

    $target   = 45;
    $campaign = "Winter Wonderland";

