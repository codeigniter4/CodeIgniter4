#############################
Upgrading from 4.4.3 to 4.4.4
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

Error Files
===========

Update the following files to show correct error messages:

- app/Views/errors/cli/error_exception.php
- app/Views/errors/html/error_exception.php

****************
Breaking Changes
****************

.. _upgrade-444-validation-with-dot-array-syntax:

Validation with Dot Array Syntax
================================

If you are using :ref:`dot array syntax <validation-dot-array-syntax>` in validation
rules, a bug where ``*`` would validate data in incorrect dimensions has been fixed.

In previous versions, the rule key ``contacts.*.name`` captured data with any
level like ``contacts.*.name``, ``contacts.*.*.name``, ``contacts.*.*.*.name``,
etc., incorrectly.

The following code explains details:

.. literalinclude:: upgrade_444/001.php
   :lines: 2-

If you have code that depends on the bug, fix the the rule key.

Validation rules matches and differs
====================================

Because bugs have been fixed in the case where ``matches`` and ``differs`` in
the Strict and Traditional rules validate data of non-string types, if you are
using these rules and validate non-string data, the validation results might be
changed (fixed).

Note that Traditional Rules should not be used to validate data that is not a
string.

The use of the `ssl_key` option in CURLRequest was removed
==========================================================

CURLRequest option `ssl_key` it's not recognized anymore.
If in use, option `ssl_key` must be replaced with option `verify` in order to define the path
to a CA bundle for CURLRequest.

CURLRequest option `verify` can also take *boolean* values as usual.

*************
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

- app/Config/App.php
- app/Config/Autoload.php
- app/Config/Boot/development.php
- app/Config/Boot/testing.php
- app/Config/Cache.php
- app/Config/Email.php
- app/Config/Filters.php
- app/Config/Kint.php
- app/Config/Modules.php
- app/Config/Publisher.php
- app/Config/Session.php
- app/Views/errors/cli/error_exception.php
- app/Views/errors/html/error_exception.php
- composer.json
- env
- spark
