##############################
Upgrading from 4.3.3 to 4.3.4
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

Redirect Status Code
====================

- Due to a bug fix, the status codes of redirects may be changed. See
  :ref:`ChangeLog v4.3.4 <v434-redirect-status-code>` and if the code is not
  what you want, :ref:`specify status codes <response-redirect-status-code>`.

Forge::modifyColumn() and NULL
==============================

A bug fix may have changed the NULL constraint in the result of
:ref:`$forge->modifyColumn() <db-forge-modifyColumn>`. See
:ref:`Change Log <v434-forge-modifycolumn>`.
To set the desired NULL constraint, change ``Forge::modifyColumn()`` to always
specify the ``null`` key.

Note that the bug may have changed unexpected NULL constraints in previous
versions.

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

- app/Config/Generators.php

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- app/Config/App.php
- app/Config/Generators.php
- composer.json
- public/index.php
