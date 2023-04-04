##############################
Upgrading from 4.3.x to 4.4.0
##############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

Breaking Changes
****************

.. _upgrade-440-uri-setsegment:

URI::setSegment() Change
========================

Dut to a bug, in previous versions an exception was not thrown if the last segment
``+2`` was specified. This bug has been fixed.

If your code depends on this bug, fix the segment number.

.. literalinclude:: upgrade_440/002.php
   :lines: 2-

Mandatory File Changes
**********************

Config Files
============

app/Config/Cookie.php
---------------------

The Cookie config items in **app/Config/App.php** are no longer used.

1. Copy **app/Config/Cookie.php** from the new framework to your **app/Config**
   directory, and configure it.
2. Remove the properties (from ``$cookiePrefix`` to ``$cookieSameSite``) in
   **app/Config/App.php**.

Breaking Enhancements
*********************

Project Files
*************

Some files in the **project space** (root, app, public, writable) received updates. Due to
these files being outside of the **system** scope they will not be changed without your intervention.

There are some third-party CodeIgniter modules available to assist with merging changes to
the project space: `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

Content Changes
===============

The following files received significant changes (including deprecations or visual adjustments)
and it is recommended that you merge the updated versions with your application:

Routing
-------

To clean up the routing system, the following changes were made:
    - New ``app/Config/Routing.php`` file that holds the settings that used to be in the Routes file.
    - The ``app/Config/Routes.php`` file was simplified so that it only contains the routes without settings and verbiage to clutter the file.
    - The ``app/Config/Routes.php`` file was moved to ``app/Routes.php`` to make it easier to find. When upgrading, you can change the ``app/Config/Routing.php` file, ``$routeFiles`` property to point to the old location if you prefer.
    - The environment-specific routes files are no longer loaded automatically. To load those, you must add them to the ``$routeFiles`` property in ``app/Config/Routing.php``.

Config
------

- @TODO

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- @TODO
