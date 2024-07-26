#############################
Upgrading from 4.5.x to 4.6.0
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

****************
Breaking Changes
****************

Exception Changes
=================

Some classes have changed the exception classes that are thrown. Some exception
classes have changed parent classes.
See :ref:`ChangeLog <v460-behavior-changes-exceptions>` for details.

If you have code that catches these exceptions, change the exception classes.

.. _upgrade-460-registrars-with-dirty-hack:

Registrars with Dirty Hack
==========================

To prevent Auto-Discovery of :ref:`registrars` from running twice, when a registrar
class is loaded or instantiated, if it instantiates a Config class (which extends
``CodeIgniter\Config\BaseConfig``), ``ConfigException`` will be raised.

This is because if Auto-Discovery of Registrars is performed twice, duplicate
values may be added to properties of Config classes.

All registrar classes (**Config/Registrar.php** in all namespaces) must be modified
so that they do not instantiate any Config class when loaded or instantiated.

If the packages/modules you are using provide such registrar classes, the registrar
classes in the packages/modules need to be fixed.

The following is an example of code that will no longer work:

.. literalinclude:: upgrade_460/001.php

Interface Changes
=================

Some interface changes have been made. Classes that implement them should update
their APIs to reflect the changes. See :ref:`ChangeLog <v460-interface-changes>`
for details.

Method Signature Changes
========================

Some method signature changes have been made. Classes that extend them should
update their APIs to reflect the changes. See :ref:`ChangeLog <v460-method-signature-changes>`
for details.

Removed Deprecated Items
========================

Some deprecated items have been removed. If you are still using these items, or
extending these classes, upgrade your code.
See :ref:`ChangeLog <v460-removed-deprecated-items>` for details.

*********************
Breaking Enhancements
*********************

.. _upgrade-460-filters-changes:

Filters Changes
===============

The ``Filters`` class has been changed to allow multiple runs of the same filter
with different arguments in before or after.

If you are extending ``Filters``, you will need to modify it to conform to the
following changes:

- The structure of the array properties ``$filters`` and ``$filtersClasses`` have
  been changed.
- The properties ``$arguments`` and ``$argumentsClass`` are no longer used.
- ``Filters`` has been changed so that the same filter class is not instantiated
  multiple times. If a filter class is used both before and after, the same instance
  is used.

*************
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

- app/Config/Feature.php
    - ``Config\Feature::$autoRoutesImproved`` has been changed to ``true``.
- app/Config/Routing.php
    - ``Config\Routing::$translateUriToCamelCase`` has been changed to ``true``.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- @TODO
