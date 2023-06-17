#############################
Upgrading from 4.3.5 to 4.3.6
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

Breaking Changes
****************

- ``AutoRouterInterface::getRoute()`` has the new second parameter ``string $httpVerb``.
  If you implement it, add the parameter.

Breaking Enhancements
*********************

- The method signatures of ``ValidationInterface::check()`` and ``Validation::check()``
  have been changed. If you implement or extend them, update the signatures.

Project Files
*************

Version 4.3.6 did not alter any executable code in project files.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- composer.json
