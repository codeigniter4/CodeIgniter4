#############################
Upgrading from 4.4.x to 4.5.0
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

Mandatory File Changes
**********************

Breaking Changes
****************

Removed Deprecated Items
========================

Some deprecated items have been removed. If you extend these classes and are
using them, upgrade your code. See :ref:`v450-removed-deprecated-items` for details.

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

Config
------

- app/Config/Database.php
    - The default value of ``charset`` in ``$default`` has been change to ``utf8mb4``.
    - The default value of ``DBCollat`` in ``$default`` has been change to ``utf8mb4_general_ci``.
    - The default value of ``DBCollat`` in ``$tests`` has been change to ``''``.
- app/Config/Feature.php
    - ``Config\Feature::$multipleFilters`` has been removed, because now
      :ref:`multiple-filters` are always enabled.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- @TODO
