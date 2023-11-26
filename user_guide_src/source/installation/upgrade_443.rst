#############################
Upgrading from 4.4.2 to 4.4.3
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

error_exception.php
===================

The following file received significant changes and
**you must merge the updated versions** with your application:

- app/Views/errors/html/error_exception.php

Project Files
*************

Some files in the **project space** (root, app, public, writable) received updates. Due to
these files being outside of the **system** scope they will not be changed without your intervention.

There are some third-party CodeIgniter modules available to assist with merging changes to
the project space: `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- app/Config/Boot/development.php
- app/Config/Boot/production.php
- app/Config/Boot/testing.php
- app/Config/Filters.php
- app/Views/errors/html/error_404.php
- app/Views/errors/html/error_exception.php
