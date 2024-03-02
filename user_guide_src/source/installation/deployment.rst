##########
Deployment
##########

.. contents::
    :local:
    :depth: 3

************
Optimization
************

Before deploying your CodeIgniter application to production, there are several
things you can do to make your application run more efficiently.

This section describes the optimization features that CodeIgniter provides.

Composer Optimization
=====================

Removing Dev Packages
---------------------

When you deploy, don't forget to run the following command:

.. code-block:: console

    composer install --no-dev

The above command will remove the Composer packages only for development
that are not needed in the production environment. This will greatly reduce
the vendor folder size.

Specifying Packages to Discover
-------------------------------

If Composer Package Auto-Discovery is enabled, all Composer packages are scanned
when needed. But there is no need to scan packages that are not CodeIgniter packages,
so specifying the packages to be scanned prevents unnecessary scanning.

See :ref:`modules-specify-composer-packages`.

Config Caching
==============

Caching the Config objects can improve performance. However, the cache must be
manually deleted when changing Config values.

See :ref:`factories-config-caching`.

FileLocator Caching
===================

Caching the file paths that FileLocator found can improve performance. However,
the cache must be manually deleted when adding/deleting/changing file paths.

See :ref:`file-locator-caching`.

PHP Preloading
==============

Every application consists of a large number of classes in many different locations.
The framework provides classes for core functionality. As defined by `PHP RFC <https://wiki.php.net/rfc/preload>`_,
Preloading is implemented as a part of the opcache on top of another (already committed) patch that introduces ``immutable``
classes and functions. They assume that the immutable part is stored in shared memory once (for all processes)
and never copied to process memory, but the variable part is specific for each process.
The patch introduced the MAP_PTR pointer data structure, that allows pointers from SHM to process memory.

.. note:: If you want to use `Preloading <https://www.php.net/manual/en/opcache.preloading.php>`_,
    we provide a `preload script <https://github.com/codeigniter4/CodeIgniter4/blob/develop/preload.php>`_.

Requirement
-----------

Preloading for classes that are used more than once in the same server is not possible. You must isolated application to ``dedicated`` server,
even if the servers are not physical machines but virtual machines or containers. Preloading keeps the relevant definitions
in memory by reading the files specified in ``opcache.preload``.

Configuration
-------------

Open ``php.ini`` or ``xx-opcache.ini`` if you have split INI configuration in PHP, and recommendation set ``opcache.preload=/path/to/preload.php`` and ``opcache.preload_user=myuser``.

.. note:: ``myuser`` is user running in your web server

Make sure you use appstater installation, If using manual installation you must change directory in include path.

.. literalinclude:: preloading/001.php

.. _deployment-to-shared-hosting-services:

*************************************
Deployment to Shared Hosting Services
*************************************

.. important::
    **index.php** is no longer in the root of the project! It has been moved inside
    the **public** folder, for better security and separation of components.

    This means that you should configure your web server to "point" to your project's
    **public** folder, and not to the project root.

Specifying the Document Root
============================

The best way is to set the document root to the **public** folder in the server
configuration::

    └── example.com/ (project folder)
        └── public/  (document root)

Check with your hosting service provider to see if you can change the document root.
Unfortunately, if you cannot change the document root, go to the next way.

Using Two Directories
=====================

The second way is to use two directories, and adjust the path.
One is for the application and the other is the default document root.

Upload the contents of the **public** folder to **public_html** (the default
document root) and the other files to the directory for the application::

    ├── example.com/ (for the application)
    │       ├── app/
    │       ├── vendor/ (or system/)
    │       └── writable/
    └── public_html/ (the default document root)
            ├── .htaccess
            ├── favicon.ico
            ├── index.php
            └── robots.txt

See
`Install CodeIgniter 4 on Shared Hosting (cPanel) <https://forum.codeigniter.com/showthread.php?tid=76779>`_
for details.

Adding .htaccess
================

The last resort is to add **.htaccess** to the project root.

It is not recommended that you place the project folder in the document root.
However, if you have no other choice, you can use this.

Place your project folder as follows, where **public_html** is the document root,
and create the **.htaccess** file::

    └── public_html/     (the default document root)
        └── example.com/ (project folder)
            ├── .htaccess
            └── public/

And edit **.htaccess** as follows:

.. code-block:: apache

    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteRule ^(.*)$ public/$1 [L]
    </IfModule>

    <FilesMatch "^\.">
        Require all denied
        Satisfy All
    </FilesMatch>
