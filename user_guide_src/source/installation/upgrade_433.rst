##############################
Upgrading from 4.3.2 to 4.3.3
##############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

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
    - Any module ``Routes.php`` files are expected to be in the namespace's root directory now. To adjust this to match the functionality of existing projects, you can cahnge the ``$modulePath`` property in ``app/Config/Routing.php`` to ``'Config/Routes.php'``.
    - The environment-specific routes files are no longer loaded automatically. To load those, you must add them to the ``$routeFiles`` property in ``app/Config/Routing.php``.

Config
------

- app/Config/Encryption.php
    - The missing property ``$cipher`` is added for CI3
      Encryption compatibility. See :ref:`encryption-compatible-with-ci3`.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- app/Common.php
- app/Config/Encryption.php
- composer.json
