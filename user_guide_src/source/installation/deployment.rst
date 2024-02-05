##########
Deployment
##########

.. contents::
    :local:
    :depth: 3

Before deploying your CodeIgniter application to production, there are several
things you can do to make your application run more efficiently.

************
Optimization
************

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
manually deleted when changing Confiig values.

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

