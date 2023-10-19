############
Code Modules
############

CodeIgniter supports a form of code modularization to help you create reusable code. Modules are typically
centered around a specific subject, and can be thought of as mini-applications within your larger application.

Any
of the standard file types within the framework are supported, like controllers, models, views, config files, helpers,
language files, etc. Modules may contain as few, or as many, of these as you like.

If you want to create a module as a Composer package, see also :doc:`../extending/composer_packages`.

.. contents::
    :local:
    :depth: 2

**********
Namespaces
**********

The core element of the modules functionality comes from the :doc:`PSR-4 compatible autoloading <../concepts/autoloader>`
that CodeIgniter uses. While any code can use the PSR-4 autoloader and namespaces, the primary way to take full advantage of
modules is to namespace your code and add it to **app/Config/Autoload.php**, in the ``$psr4`` property.

For example, let's say we want to keep a simple blog module that we can re-use between applications. We might create
folder with our company name, Acme, to store all of our modules within. We will put it right alongside our **app**
directory in the main project root::

    acme/        // New modules directory
    app/
    public/
    system/
    tests/
    writable/

Open **app/Config/Autoload.php** and add the ``Acme\Blog`` namespace to the ``$psr4`` array property:

.. literalinclude:: modules/001.php

Now that this is set up, we can access any file within the **acme/Blog** folder through the ``Acme\Blog`` namespace. This alone
takes care of 80% of what is needed for modules to work, so you should be sure to familiarize yourself with namespaces
and become comfortable with their use. Several file types will be scanned for automatically through all defined namespaces - a crucial ingredient for working with modules.

A common directory structure within a module will mimic the main application folder::

    acme/
        Blog/
            Config/
            Controllers/
            Database/
                Migrations/
                Seeds/
            Helpers/
            Language/
                en/
            Libraries/
            Models/
            Views/

Of course, there is nothing forcing you to use this exact structure, and you should organize it in the manner that
best suits your module, leaving out directories you don't need, creating new directories for Entities, Interfaces,
or Repositories, etc.

***************************
Autoloading Non-class Files
***************************

More often than not that your module will not contain only PHP classes but also others like procedural
functions, bootstrapping files, module constants files, etc. which are not normally loaded the way classes
are loaded. One approach for this is using ``require``-ing the file(s) at the start of the file where it
would be used.

Another approach provided by CodeIgniter is to autoload these *non-class* files like how you would autoload
your classes. All we need to do is provide the list of paths to those files and include them in the
``$files`` property of your **app/Config/Autoload.php** file.

.. literalinclude:: modules/002.php

.. _auto-discovery:

**************
Auto-Discovery
**************

Many times, you will need to specify the full namespace to files you want to include, but CodeIgniter can be
configured to make integrating modules into your applications simpler by automatically discovering many different
file types, including:

- :doc:`Events <../extending/events>`
- :doc:`Filters <../incoming/filters>`
- :ref:`registrars`
- :doc:`Route files <../incoming/routing>`
- :doc:`Services <../concepts/services>`

This is configured in the file **app/Config/Modules.php**.

The auto-discovery system works by scanning for particular directories and files within psr4 namespaces that have been defined in **Config/Autoload.php** and Composer packages.

The discovery process would look for discoverable items on that path and should, for example, find the routes file at **acme/Blog/Config/Routes.php**.

Enable/Disable Discover
=======================

You can turn on or off all auto-discovery in the system with the ``$enabled`` class variable. False will disable
all discovery, optimizing performance, but negating the special capabilities of your modules and Composer packages.

Specify Discovery Items
=======================

With the ``$aliases`` option, you can specify which items are automatically discovered. If the item is not
present, then no auto-discovery will happen for that item, but the others in the array will still be discovered.

Discovery and Composer
======================

Packages installed via Composer using PSR-4 namespaces will also be discovered by default.
PSR-0 namespaced packages will not be detected.

.. _modules-specify-composer-packages:

Specify Composer Packages
-------------------------

.. versionadded:: 4.3.0

To avoid wasting time scanning for irrelevant Composer packages, you can manually specify packages to discover by editing the ``$composerPackages`` variable in **app/Config/Modules.php**:

.. literalinclude:: modules/013.php

Alternatively, you can specify which packages to exclude from discovery.

.. literalinclude:: modules/014.php

Disable Composer Package Discovery
----------------------------------

If you do not want all of Composer's known directories to be scanned when locating files, you can turn this off
by editing the ``$discoverInComposer`` variable in **app/Config/Modules.php**:

.. literalinclude:: modules/004.php

******************
Working with Files
******************

This section will take a look at each of the file types (controllers, views, language files, etc) and how they can
be used within the module. Some of this information is described in more detail in the relevant location of the user
guide, but is being reproduced here so that it's easier to grasp how all of the pieces fit together.

Routes
======

By default, :doc:`routes <../incoming/routing>` are automatically scanned for within modules. It can be turned off in
the **Modules** config file, described above.

.. note:: Since the files are being included into the current scope, the ``$routes`` instance is already defined for you.
    It will cause errors if you attempt to redefine that class.

When working with modules, it can be a problem if the routes in the application contain wildcards.
In that case, see :ref:`routing-priority`.

.. _modules-filters:

Filters
=======

.. deprecated:: 4.4.2

.. note:: This feature is deprecated. Use :ref:`registrars` instead like the
    following:

    .. literalinclude:: modules/015.php

By default, :doc:`filters <../incoming/filters>` are automatically scanned for within modules.
It can be turned off in the **Modules** config file, described above.

.. note:: Since the files are being included into the current scope, the ``$filters`` instance is already defined for you.
    It will cause errors if you attempt to redefine that class.

In the module's **Config/Filters.php** file, you need to define the aliases of the filters you use:

.. literalinclude:: modules/005.php

Controllers
===========

Controllers outside of the main **app/Controllers** directory cannot be automatically routed by URI detection,
but must be specified within the Routes file itself:

.. literalinclude:: modules/006.php

To reduce the amount of typing needed here, the **group** routing feature is helpful:

.. literalinclude:: modules/007.php

Config Files
============

No special change is needed when working with configuration files. These are still namespaced classes and loaded
with the ``new`` command:

.. literalinclude:: modules/008.php

Config files are automatically discovered whenever using the :php:func:`config()` function that is always available, and you pass a short classname to it.

.. note:: We don't recommend you use the same short classname in modules.
    Modules that need to override or add to known configurations in **app/Config/** should use :ref:`Implicit Registrars <registrars>`.

.. note:: Prior to v4.4.0, ``config()`` finds the file in **app/Config/** when there
    is a class with the same shortname,
    even if you specify a fully qualified class name like ``config(\Acme\Blog\Config\Blog::class)``.
    This behavior has been fixed in v4.4.0, and returns the specified instance.

Migrations
==========

Migration files will be automatically discovered within defined namespaces. All migrations found across all
namespaces will be run every time.

Seeds
=====

Seed files can be used from both the CLI and called from within other seed files as long as the full namespace
is provided. If calling on the CLI, you will need to provide double backslashes:


For Unix:

.. code-block:: console

    php spark db:seed Acme\\Blog\\Database\\Seeds\\TestPostSeeder

For Windows:

.. code-block:: console

    php spark db:seed Acme\Blog\Database\Seeds\TestPostSeeder

Helpers
=======

Helpers will be automatically discovered within defined namespaces when using the ``helper()`` function, as long as it
is within the namespaces **Helpers** directory:

.. literalinclude:: modules/009.php

You can specify namespaces. See :ref:`helpers-loading-from-non-standard-locations` for details.

Language Files
==============

Language files are located automatically from defined namespaces when using the ``lang()`` method, as long as the
file follows the same directory structures as the main application directory.

Libraries
=========

Libraries are always instantiated by their fully-qualified class name, so no special access is provided:

.. literalinclude:: modules/010.php

Models
======

If you instantiate models with ``new`` keyword by their fully-qualified class names, no special access is provided:

.. literalinclude:: modules/011.php

Model files are automatically discovered whenever using the :php:func:`model()` function that is always available.

.. note:: We don't recommend you use the same short classname in modules.

.. note:: ``model()`` finds the file in **app/Models/** when there is a class with the same shortname,
    even if you specify a fully qualified class name like ``model(\Acme\Blog\Model\PostModel::class)``.
    This is because ``model()`` is a wrapper for the ``Factories`` class which uses ``preferApp`` by default. See :ref:`factories-loading-class` for more information.

Views
=====

Views can be loaded using the class namespace as described in the :doc:`views </outgoing/views>` documentation:

.. literalinclude:: modules/012.php
