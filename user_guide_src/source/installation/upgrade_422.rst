#############################
Upgrading from 4.2.1 to 4.2.2
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

Breaking Changes
****************

Web Page Caching Bug Fix
========================

- :doc:`../general/caching` now caches the Response data after :ref:`after-filters` are executed.
- For example, if you enable :ref:`secureheaders`, the Response headers are now sent when the page comes from the cache.

.. important:: If you have written **code based on this bug** that assumes changes to the Response in "after" filters are not cached then **sensitive information could be cached and compromised**. If this is the case, change your code to disable caching of the page.

Others
======

- The method ``Forge::createTable()`` no longer executes a ``CREATE TABLE IF NOT EXISTS``.  When `$ifNotExists` is true, if the table is not found in ``$db->tableExists($table)`` then ``CREATE TABLE`` is executed.
- The second parameter ``$ifNotExists`` of ``Forge::_createTable()`` is deprecated. It is no longer used and will be removed in a future release.
- When you use :php:func:`random_string()` with the first parameter ``'crypto'``, now if you set the second parameter ``$len`` to an odd number, ``InvalidArgumentException`` will be thrown. Change the parameter to an even number.

Breaking Enhancements
*********************


Project Files
*************

Numerous files in the **project space** (root, app, public, writable) received updates. Due to
these files being outside of the **system** scope they will not be changed without your intervention.
There are some third-party CodeIgniter modules available to assist with merging changes to
the project space: `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

.. note:: Except in very rare cases for bug fixes, no changes made to files for the project space
    will break your application. All changes noted here are optional until the next major version,
    and any mandatory changes will be covered in the sections above.

Content Changes
===============

* app/Views/errors/html/error_404.php
* app/Views/welcome_message.php
* public/index.php
* spark

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

* app/Config/App.php
* app/Config/Constants.php
* app/Config/Logger.php
* app/Config/Paths.php
* app/Views/errors/html/error_404.php
* app/Views/welcome_message.php
