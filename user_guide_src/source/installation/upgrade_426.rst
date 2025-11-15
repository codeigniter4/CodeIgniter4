#############################
Upgrading from 4.2.5 to 4.2.6
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2


Project Files
*************

A few files in the **project space** (root, app, public, writable) received cosmetic updates.
You need not touch these files at all. There are some third-party CodeIgniter modules available
to assist with merging changes to the project space: `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

* app/Config/App.php
* app/Config/ContentSecurityPolicy.php
* app/Config/Routes.php
* app/Config/Validation.php
