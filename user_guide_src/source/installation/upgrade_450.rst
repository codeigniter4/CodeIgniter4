#############################
Upgrading from 4.4.x to 4.5.0
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

Mandatory File Changes
**********************

Breaking Changes
****************

.. _upgrade-450-nested-route-groups-and-options:

Nested Route Groups and Options
===============================

A bug that prevented options passed to outer ``group()`` from being merged with
options in inner ``group()`` has been fixed.

Check and correct your route configuration as it could change the values of the
options applied.

For example,

.. code-block:: php

    $routes->group('admin', ['filter' => 'csrf'], static function ($routes) {
        $routes->get('/', static function () {
            // ...
        });

        $routes->group('users', ['namespace' => 'Users'], static function ($routes) {
            $routes->get('/', static function () {
                // ...
            });
        });
    });

Now the ``csrf`` filter is executed for both the route ``admin`` and ``admin/users``.
In previous versions, it is executed only for the route ``admin``.
See also :ref:`routing-nesting-groups`.

Method Signature Changes
========================

Some method signature changes have been made. Classes that extend them should
update their APIs to reflect the changes. See :ref:`ChangeLog <v450-method-signature-changes>`
for details.

.. _upgrade-450-filter-execution-order:

Filter Execution Order
======================

The order in which Controller Filters are executed has changed.
If you wish to maintain the same execution order as in previous versions, set
``true`` to ``Config\Feature::$oldFilterOrder``. See also :ref:`filter-execution-order`.

1. The order of execution of filter groups has been changed.

    Before Filters::

        Previous: route → globals → methods → filters
             Now: globals → methods → filters → route

    After Filters::

        Previous: route → globals → filters
             Now: route → filters → globals

2. The After Filters in *Route* filters and *Filters* filters execution order is now
reversed.

    When you have the following configuration:

    .. code-block:: php

        // In app/Config/Routes.php
        $routes->get('/', 'Home::index', ['filter' => ['route1', 'route2']]);

        // In app/Config/Filters.php
        public array $filters = [
            'filter1' => ['before' => '*', 'after' => '*'],
            'filter2' => ['before' => '*', 'after' => '*'],
        ];

    Before Filters::

        Previous: route1 → route2 → filter1 → filter2
             Now: filter1 → filter2 → route1 → route2

    After Filters::

        Previous: route1 → route2 → filter1 → filter2
             Now: route2 → route1 → filter2 → filter1

FileLocator::findQualifiedNameFromPath()
========================================

In previous versions, ``FileLocator::findQualifiedNameFromPath()`` returns Fully
Qualified Classnames with a leading ``\``. Now the leading ``\`` has been removed.

If you have code that expects a leading ``\``, fix it.

BaseModel::getIdValue()
=======================

The ``BaseModel::getIdValue()`` has been changed to ``abstract``, and the implementation
has been removed.

If you extneds ``BaseModel``, implement the ``getIdValue()`` method in the child class.

Factories
=========

:doc:`../concepts/factories` has been changed to a final class.
In the unlikely event, you have inherited the Factories, stop inheriting and
copy the code into your Factories class.

Removed Deprecated Items
========================

Some deprecated items have been removed. If you extend these classes and are
using them, upgrade your code. See :ref:`ChangeLog <v450-removed-deprecated-items>` for details.

Auto Routing (Legacy) 
=======================

If the auto routing cannot found the controller, it will throw page not found exception before the filter executed.

Breaking Enhancements
*********************

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

- app/Config/Database.php
    - The default value of ``charset`` in ``$default`` has been change to ``utf8mb4``.
    - The default value of ``DBCollat`` in ``$default`` has been change to ``utf8mb4_general_ci``.
    - The default value of ``DBCollat`` in ``$tests`` has been change to ``''``.
- app/Config/Feature.php
    - ``Config\Feature::$multipleFilters`` has been removed, because now
      :ref:`multiple-filters` are always enabled.
- app/Config/Kint.php
    - It no longer extends ``BaseConfig`` because enabling
      :ref:`factories-config-caching` could cause errors.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- @TODO
