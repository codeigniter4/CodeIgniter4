#################
Autoloading Files
#################

.. contents::
    :local:
    :depth: 2

Every application consists of a large number of classes in many different locations.
The framework provides classes for core functionality. Your application will have a
number of libraries, models, and other entities to make it work. You might have third-party
classes that your project is using. Keeping track of where every single file is, and
hard-coding that location into your files in a series of ``requires()`` is a massive
headache and very error-prone. That's where autoloaders come in.

CodeIgniter4 Autoloader
***********************

CodeIgniter provides a very flexible autoloader that can be used with very little configuration.
It can locate individual namespaced classes that adhere to
`PSR-4 <https://www.php-fig.org/psr/psr-4/>`_ autoloading
directory structures.

The autoloader works great by itself, but can also work with other autoloaders, like
`Composer <https://getcomposer.org>`_, or even your own custom autoloaders, if needed.
Because they're all registered through
`spl_autoload_register <https://www.php.net/manual/en/function.spl-autoload-register.php>`_,
they work in sequence and don't get in each other's way.

The autoloader is always active, being registered with ``spl_autoload_register()`` at the
beginning of the framework's execution.

.. important:: You should always be careful about the case of filenames. Many
    developers develop on case-insensitive file systems on Windows or macOS.
    However, most server environments use case-sensitive file systems. If the
    file name case is incorrect, the autoloader cannot find the file on the
    server.

Configuration
*************

Initial configuration is done in **app/Config/Autoload.php**. This file contains two primary
arrays: one for the classmap, and one for PSR-4 compatible namespaces.

Namespaces
**********

The recommended method for organizing your classes is to create one or more namespaces for your
application's files. This is most important for any business-logic related classes, entity classes,
etc. The ``$psr4`` array in the configuration file allows you to map the namespace to the directory
those classes can be found in:

.. literalinclude:: autoloader/001.php

The key of each row is the namespace itself. This does not need a trailing back slash.
The value is the location to the directory the classes can be found in.

.. note:: You can check the namespace configuration by ``spark namespaces`` command:

    .. code-block:: console

        php spark namespaces

By default, the application directory is namespace to the ``App`` namespace. You must namespace the controllers,
libraries, or models in the application directory, and they will be found under the ``App`` namespace.

You may change this namespace by editing the **app/Config/Constants.php** file and setting the
new namespace value under the ``APP_NAMESPACE`` setting:

.. literalinclude:: autoloader/002.php
   :lines: 2-

You will need to modify any existing files that are referencing the current namespace.

.. important:: Config files are namespaced in the ``Config`` namespace, not in ``App\Config`` as you might
    expect. This allows the core system files to always be able to locate them, even when the application
    namespace has changed.

Classmap
********

The classmap is used extensively by CodeIgniter to eke the last ounces of performance out of the system
by not hitting the file-system with extra ``is_file()`` calls. You can use the classmap to link to
third-party libraries that are not namespaced:

.. literalinclude:: autoloader/003.php

The key of each row is the name of the class that you want to locate. The value is the path to locate it at.

Composer Support
****************

Composer support is automatically initialized by default. By default, it looks for Composer's autoload file at
``ROOTPATH . 'vendor/autoload.php'``. If you need to change the location of that file for any reason, you can modify
the value defined in **app/Config/Constants.php**.

.. note:: If the same namespace is defined in both CodeIgniter and Composer, CodeIgniter's autoloader will be
    the first one to get a chance to locate the file.
