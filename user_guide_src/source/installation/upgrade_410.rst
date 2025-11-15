######################################
Upgrading from 4.0.5 to 4.1.0 or 4.1.1
######################################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

Breaking Changes
****************

Legacy Autoloading
==================

``Autoloader::loadLegacy()`` method was originally for transition to CodeIgniter v4. Since v4.1.0,
this support was removed. All classes must be namespaced.
