###############################
Upgrading from 4.2.10 to 4.2.11
###############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

Breaking Changes
****************

.. _upgrade-4211-proxyips:

Config\\App::$proxyIPs
======================

The config value format has been changed. Now you must set your proxy IP address and the HTTP header name for the client IP address pair as an array::

    public $proxyIPs = [
            '10.0.1.200'     => 'X-Forwarded-For',
            '192.168.5.0/24' => 'X-Forwarded-For',
    ];

``ConfigException`` will be thrown for old format config value.

Project Files
*************

Version ``4.2.11`` did not alter any executable code in project files.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

* app/Config/App.php
* app/Config/Autoload.php
* app/Config/Logger.php
* app/Config/Toolbar.php
* app/Views/welcome_message.php
* composer.json
