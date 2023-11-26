#############
Configuration
#############

Every framework uses configuration files to define numerous parameters and
initial settings. CodeIgniter configuration files define simple classes where
the required settings are public properties.

Unlike many other frameworks, CodeIgniter configurable items aren't contained in
a single file. Instead, each class that needs configurable items will have a
configuration file with the same name as the class that uses it. You will find
the application configuration files in the **app/Config** folder.

.. contents::
    :local:
    :depth: 2

Working with Configuration Files
********************************

Getting a Config Object
=======================

You can access configuration files for your classes in several different ways.

new keyword
-----------

By using the ``new`` keyword to create an instance:

.. literalinclude:: configuration/001.php

.. _configuration-config:

config()
--------

By using the :php:func:`config()` function:

.. literalinclude:: configuration/002.php

If no namespace is provided, it will look for the file in the **app/Config**
folder first, and if not found, look for in the **Config** folder in all defined
namespaces.

All of the configuration files that ship with CodeIgniter are namespaced with
``Config``. Using this namespace in your application will provide the best
performance since it knows exactly where to find the files.

.. note:: Prior to v4.4.0, ``config()`` finds the file in **app/Config/** when there
    is a class with the same shortname,
    even if you specify a fully qualified class name like ``config(\Acme\Blog\Config\Blog::class)``.
    This behavior has been fixed in v4.4.0, and returns the specified instance.

Getting a Config Property
=========================

All configuration object properties are public, so you access the settings like any other property:

.. literalinclude:: configuration/003.php

Creating Configuration Files
****************************

When you need a new configuration, first you create a new file at your desired location.
The default file location (recommended for most cases) is **app/Config**.

You can put configuration files in any **Config** folder by using a different namespace.

The class should use the appropriate namespace, and it should extend
``CodeIgniter\Config\BaseConfig`` to ensure that it can receive environment-specific settings.

Define the class and fill it with public properties that represent your settings:

.. literalinclude:: configuration/004.php

Environment Variables
*********************

One of today's best practices for application setup is to use Environment Variables. One reason for this is that Environment Variables are easy to change between deploys without changing any code. Configuration can change a lot across deploys, but code does not. For instance, multiple environments, such as the developer's local machine and the production server, usually need different configuration values for each particular setup.

Environment Variables should also be used for anything private such as passwords, API keys, or other sensitive data.

.. _dotenv-file:

Dotenv File
===========

CodeIgniter makes it simple and painless to set Environment Variables by using a "dotenv" file. The term comes from the file name, which starts with a dot before the text "env".

Creating Dotenv File
--------------------

CodeIgniter expects the **.env** file to be at the root of your project alongside the
**app** directories. There is a template file distributed with CodeIgniter that's
located at the project root named **env** (Notice there's no dot (``.``) at the start?).

It has a large collection of variables your project might use that have been assigned
empty, dummy, or default values. You can use this file as a starting place for your
application by either renaming the template to **.env**, or by making a copy of it named **.env**.

.. warning:: Make sure the **.env** file is NOT tracked by your version control system. For *git* that means adding it to **.gitignore**. Failure to do so could result in sensitive credentials being exposed to the public.

Setting Variables
-----------------

Settings are stored in **.env** files as a simple a collection of name/value pairs separated by an equal sign.
::

    S3_BUCKET = dotenv
    SECRET_KEY = super_secret_key
    CI_ENVIRONMENT = development

When your application runs, **.env** will be loaded automatically, and the variables put
into the environment. If a variable already exists in the environment, it will NOT be
overwritten.

Getting Variables
-----------------

The loaded environment variables are accessed using any of the following:
``getenv()``, ``$_SERVER``, or ``$_ENV``.

.. literalinclude:: configuration/005.php

.. warning:: Note that your settings from the **.env** file are added to ``$_SERVER`` and ``$_ENV``. As a side effect, this means that if your CodeIgniter application is (for example) generating a ``var_dump($_ENV)`` or ``phpinfo()`` (for debugging or other valid reasons), or a detailed error report in the ``development`` environment is shown, **your secure credentials are publicly exposed**.

Nesting Variables
-----------------

To save on typing, you can reuse variables that you've already specified in the file by wrapping the
variable name within ``${...}``:

::

    BASE_DIR = "/var/webroot/project-root"
    CACHE_DIR = "${BASE_DIR}/cache"
    TMP_DIR = "${BASE_DIR}/tmp"

Namespaced Variables
--------------------

There will be times when you will have several variables with the same name.
The system needs a way of knowing what the correct setting should be.
This problem is solved by "*namespacing*" the variables.

Namespaced variables use a dot notation to qualify variable names so they will be unique
when incorporated into the environment. This is done by including a distinguishing
prefix followed by a dot (.), and then the variable name itself.

::

    // not namespaced variables
    name = "George"
    db = my_db

    // namespaced variables
    address.city = "Berlin"
    address.country = "Germany"
    frontend.db = sales
    backend.db = admin
    BackEnd.db = admin

.. _env-var-namespace-separator:

Namespace Separator
-------------------

Some environments, e.g., Docker, CloudFormation, do not permit variable name with dots (``.``). In such case, since v4.1.5, you could also use underscores (``_``) as a separator.

::

    // namespaced variables with underscore
    app_forceGlobalSecureRequests = true
    app_CSPEnabled = true

Configuration Classes and Environment Variables
***********************************************

When you instantiate a configuration class, any *namespaced* environment variables
are considered for merging into the configuration object's properties.

.. important:: You cannot add a new property by setting environment variables,
    nor change a scalar value to an array. See :ref:`env-var-replacements-for-data`.

.. note:: This feature is implemented in the ``CodeIgniter\Config\BaseConfig``
    class. So it will not work with a few files in the **app/Config** folder
    that do not extends the class.

If the prefix of a namespaced variable exactly matches the namespace of the configuration
class, then the trailing part of the setting (after the dot) is treated as a configuration
property. If it matches an existing configuration property, the environment variable's
value will replace the corresponding value from the configuration file. If there is no match,
the configuration class properties are left unchanged. In this usage, the prefix must be
the full (case-sensitive) namespace of the class.

::

    Config\App.forceGlobalSecureRequests = true
    Config\App.CSPEnabled = true

.. note:: Both the namespace prefix and the property name are case-sensitive. They must exactly match the full namespace and property names as defined in the configuration class file.

The same holds for a *short prefix*, which is a namespace using only the lowercase version of
the configuration class name. If the short prefix matches the class name,
the value from **.env** replaces the configuration file value.

::

    app.forceGlobalSecureRequests = true
    app.CSPEnabled = true

Since v4.1.5, you can also write with underscores::

    app_forceGlobalSecureRequests = true
    app_CSPEnabled = true

.. note:: When using the *short prefix* the property names must still exactly match the class defined name.

.. _env-var-replacements-for-data:

Environment Variables as Replacements for Data
==============================================

It is very important to always remember that environment variables contained in your **.env** are
**only replacements for existing data**.

Simply put, you can change only the property value that exists in the Config class
by setting it in your **.env**.

You cannot add a property that is not defined in the Config class, nor can you
change it to an array if the value of the defined property is a scalar.

For example, you cannot just put ``app.myNewConfig = foo`` in your **.env** and
expect your ``Config\App`` to magically have that property and value at run time.

When you have the property ``$default = ['encrypt' => false]`` in your
``Config\Database``, you cannot change the ``encrypt`` value to an array even if
you put ``database.default.encrypt.ssl_verify = true`` in your **.env**.
If you want to do like that, see
:ref:`Database Configuration <database-config-with-env-file>`.

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

If this was referring to a SimpleConfig configuration object, the above example would be treated as:

.. literalinclude:: configuration/006.php

Any other elements of the ``$address`` property would be unchanged.

You can also use the array property name as a prefix. If the environment file
held the following then the result would be the same as above.

::

    // array namespaced variables
    Config\SimpleConfig.address.city = "Berlin"
    address.country = "Germany"

Handling Different Environments
*******************************

Configuring multiple environments is easily accomplished by using a separate **.env** file with values modified to meet that environment's needs.

The file should not contain every possible setting for every configuration class used by the application. In truth, it should include only those items that are specific to the environment or are sensitive details like passwords and API keys and other information that should not be exposed. But anything that changes between deployments is fair-game.

In each environment, place the **.env** file in the project's root folder. For most setups, this will be the same level as the ``app`` directories.

Do not track **.env** files with your version control system. If you do, and the repository is made public, you will have put sensitive information where everybody can find it.

.. _registrars:

Registrars
**********

"Registrars" are any other classes which might provide additional configuration properties.
Registrars provide a means of altering a configuration at runtime across namespaces and files.

Registrars work if :ref:`auto-discovery` is enabled in :doc:`Modules </general/modules>`.
It alters configuration properties when the Config object is instantiated.

There are two ways to implement a Registrar: **implicit** and **explicit**.

.. note:: Values from **.env** always take priority over Registrars.

Implicit Registrars
===================

Implicit Registrars can change any Config class properties.

Any namespace may define implicit registrars by using the **Config/Registrar.php**
file. These files are classes whose methods are named for each configuration class
you wish to extend.

For example, a third-party module or Composer package might
wish to supply an additional template to ``Config\Pager`` without overwriting whatever a developer has
already configured. In **src/Config/Registrar.php** there would be a ``Registrar`` class with
the single ``Pager()`` method (note the case-sensitivity):

.. literalinclude:: configuration/007.php

Registrar methods must always return an array, with keys corresponding to the properties
of the target config file. Existing values are merged, and Registrar properties have
overwrite priority.

Explicit Registrars
===================

Explicit Registrars can only change the Config class properties in which they are
registered.

A configuration file can also specify any number of registrars explicitly.
This is done by adding a ``$registrars`` property to your configuration file,
holding an array of the names of candidate registrars:

.. literalinclude:: configuration/008.php

In order to act as a "registrar" the classes so identified must have a
static function with the same name as the configuration class, and it should return an associative
array of property settings.

When your configuration object is instantiated, it will loop over the
designated classes in ``$registrars``. For each of these classes it will invoke
the method named for the configuration class and incorporate any returned properties.

A sample configuration class setup for this:

.. literalinclude:: configuration/009.php

... and the associated regional sales model might look like:

.. literalinclude:: configuration/010.php

With the above example, when ``MySalesConfig`` is instantiated, it will end up with
the three properties declared, but the value of the ``$target`` property will be overridden
by treating ``RegionalSales`` as a "registrar". The resulting configuration properties:

.. literalinclude:: configuration/011.php
