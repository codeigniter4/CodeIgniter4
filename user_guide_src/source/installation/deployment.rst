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

If you want to use `Preloading <https://www.php.net/manual/en/opcache.preloading.php>`_,
we provide a
`preload script <https://github.com/codeigniter4/CodeIgniter4/blob/develop/preload.php>`_.

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
