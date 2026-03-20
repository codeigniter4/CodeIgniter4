#############################
Upgrading from 4.7.0 to 4.7.1
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

**********************
Mandatory File Changes
**********************

Worker Mode
===========

If you are using Worker Mode, you must update **public/frankenphp-worker.php** after
upgrading. The easiest way is to re-run the install command:

.. code-block:: console

    php spark worker:install --force

****************
Breaking Changes
****************

*********************
Breaking Enhancements
*********************

Database Connection Property Casting
======================================

``BaseConnection`` now casts string values coming from ``.env`` overrides to match
the declared type of each connection property. This affects properties that are
``null`` in the config array and then set via ``.env`` - such as SQLite3's
``synchronous`` or ``busyTimeout`` - which previously arrived as strings and were
stored without conversion.

If you extended the SQLite3 handler, review your custom typed properties and update
them if needed.

*************
Project Files
*************

Some files in the **project space** (root, app, public, writable) received updates. Due to
these files being outside of the **system** scope they will not be changed without your intervention.

.. note:: There are some third-party CodeIgniter modules available to assist
    with merging changes to the project space:
    `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

Content Changes
===============

The following files received significant changes (including deprecations or visual adjustments)
and it is recommended that you merge the updated versions with your application:

Config
------

- app/Config/WorkerMode.php
    - ``Config\WorkerMode::$resetEventListeners`` has been added, with a default
      value set to ``[]``. See :ref:`worker-mode-reset-event-listeners` for details.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- app/Config/WorkerMode.php
