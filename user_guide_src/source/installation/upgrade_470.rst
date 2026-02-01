#############################
Upgrading from 4.6.x to 4.7.0
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

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

- app/Config/Migrations.php
    - ``Config\Migrations::$lock`` has been added, with a default value set to ``false``.

These files are new in this release:

- app/Config/Hostnames.php
- app/Config/WorkerMode.php

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- app/Config/CURLRequest.php
- app/Config/Cache.php
- app/Config/ContentSecurityPolicy.php
- app/Config/Email.php
- app/Config/Encryption.php
- app/Config/Format.php
- app/Config/Hostnames.php
- app/Config/Images.php
- app/Config/Migrations.php
- app/Config/Optimize.php
- app/Config/Paths.php
- app/Config/Routing.php
- app/Config/Session.php
- app/Config/Toolbar.php
- app/Config/UserAgents.php
- app/Config/View.php
- app/Config/WorkerMode.php
- public/index.php
- spark
