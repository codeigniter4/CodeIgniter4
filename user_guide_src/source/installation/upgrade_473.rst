#############################
Upgrading from 4.7.2 to 4.7.3
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

File Validation
===============

The ``ext_in`` file upload validation rule now checks the client filename
extension and verifies that the detected MIME type is associated with that
extension. Previously, ``ext_in`` only checked the MIME-derived guessed
extension.

This means files with no client filename extension, or files whose client
filename extension does not match the detected MIME type, now fail ``ext_in``
validation. If your application intentionally accepts such files, remove
``ext_in`` from those validation rules and use a custom validation rule that
matches your application's requirements.

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

- app/Config/Database.php
- app/Config/Events.php
- app/Config/Routes.php
- app/Config/View.php

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- app/Config/Database.php
- app/Config/Events.php
- app/Config/Routes.php
- app/Config/View.php
