#############################
Upgrading from 4.6.0 to 4.6.1
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

- app/Config/Mimes.php
    - ``Config\Mimes::$mimes`` has been updated to include the ``application/octet-stream`` and
        ``model/stl`` MIME types for the ``.stl`` file extension.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- app/Config/Autoload.php
- app/Config/Cache.php
- app/Config/DocTypes.php
- app/Config/Mimes.php
- app/Config/Modules.php
- app/Config/Optimize.php
- app/Config/Paths.php
- app/Views/errors/html/debug.css
- preload.php
- public/index.php
- spark
