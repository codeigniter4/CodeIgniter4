#############################
Upgrading from 4.1.4 to 4.1.5
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

Breaking Changes
================

Changes for set() method in BaseBuilder and Model class
-------------------------------------------------------

The casting for the ``$value`` parameter has been removed to fix a bug where passing parameters as array and string
to the ``set()`` method were handled differently. If you extended the ``BaseBuilder`` class or ``Model`` class yourself
and modified the ``set()`` method, then you need to change its definition from
``public function set($key, ?string $value = '', ?bool $escape = null)`` to
``public function set($key, $value = '', ?bool $escape = null)``.

Session DatabaseHandler's Database Table Change
-----------------------------------------------

The types of the following columns in the session table have been changed for optimization.

- MySQL
    - ``timestamp``
- PostgreSQL
    - ``ip_address``
    - ``timestamp``
    - ``data``

Update the definition of the session table. See the :doc:`/libraries/sessions` for the new definition.

The change was introduced in v4.1.2. But due to `a bug <https://github.com/codeigniter4/CodeIgniter4/issues/4807>`_,
the DatabaseHandler Driver did not work properly.

CSRF Protection
---------------

Because of a bug fix,
now CSRF protection works on not only **POST** but also **PUT/PATCH/DELETE** requests when CSRF filter is applied.

When you use **PUT/PATCH/DELETE** requests, you need to send CSRF token. Or remove the CSRF filter
for such requests if you don't need CSRF protection for them.

If you want the same behavior as the previous version, set the CSRF filter like the following in **app/Config/Filters.php**:

.. literalinclude:: upgrade_415/001.php

Protecting **GET** method needs only when you use ``form_open()`` auto-generation of CSRF field.

.. Warning:: In general, if you use ``$methods`` filters, you should :ref:`disable Auto Routing (Legacy) <use-defined-routes-only>`
    because :ref:`auto-routing-legacy` permits any HTTP method to access a controller.
    Accessing the controller with a method you don't expect could bypass the filter.

CURLRequest Header Change
-------------------------

In the previous version, if you didn't provide your own headers, ``CURLRequest`` would send the request-headers from the browser.
The bug was fixed. If your requests depend on the headers, your requests might fail after upgrading.
In this case, add the necessary headers manually.
See :ref:`CURLRequest Class <curlrequest-request-options-headers>` for how to add.

Query Builder Changes
---------------------

For optimization and a bug fix, the following behaviors, mostly used in testing, have been changed.

- When you use ``insertBatch()`` and ``updateBatch()``, the return value of ``$query->getOriginalQuery()`` has changed. It no longer returns the query with the binded parameters, but the actual query that was run.
- If ``testMode`` is ``true``, ``insertBatch()`` will return an SQL string array instead of the number of affected rows. This change was made so that the returned data type is the same as the ``updateBatch()`` method.

Breaking Enhancements
=====================

.. _upgrade-415-multiple-filters-for-a-route:

Multiple Filters for a Route
----------------------------

A new feature to set multiple filters for a route.

.. important:: This feature is disabled by default. Because it breaks backward compatibility.

If you want to use this, you need to set the property ``$multipleFilters`` ``true`` in ``app/Config/Feature.php``.
If you enable it:

- ``CodeIgniter\CodeIgniter::handleRequest()`` uses
    - ``CodeIgniter\Filters\Filters::enableFilters()``, instead of ``enableFilter()``
- ``CodeIgniter\CodeIgniter::tryToRouteIt()`` uses
    - ``CodeIgniter\Router\Router::getFilters()``, instead of ``getFilter()``
- ``CodeIgniter\Router\Router::handle()`` uses
    - the property ``$filtersInfo``, instead of ``$filterInfo``
    - ``CodeIgniter\Router\RouteCollection::getFiltersForRoute()``, instead of ``getFilterForRoute()``

If you extended the above classes, then you need to change them.

The following methods and a property have been deprecated:

- ``CodeIgniter\Filters\Filters::enableFilter()``
- ``CodeIgniter\Router\Router::getFilter()``
- ``CodeIgniter\Router\RouteCollection::getFilterForRoute()``
- ``CodeIgniter\Router\RouteCollection``'s property ``$filterInfo``

See :ref:`applying-filters` for the functionality.

Project Files
=============

Numerous files in the project space (root, app, public, writable) received updates. Due to
these files being outside of the system scope they will not be changed without your intervention.
There are some third-party CodeIgniter modules available to assist with merging changes to
the project space: `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

.. note:: Except in very rare cases for bug fixes, no changes made to files for the project space
    will break your application. All changes noted here are optional until the next major version,
    and any mandatory changes will be covered in the sections above.

Content Changes
---------------

The following files received significant changes (including deprecations or visual adjustments)
and it is recommended that you merge the updated versions with your application:

* ``app/Config/CURLRequest.php``
* ``app/Config/Cache.php``
* ``app/Config/Feature.php``
* ``app/Config/Generators.php``
* ``app/Config/Publisher.php``
* ``app/Config/Security.php``
* ``app/Views/welcome_message.php``

All Changes
-----------

This is a list of all files in the project space that received changes;
many will be simple comments or formatting that have no effect on the runtime:

* ``app/Config/CURLRequest.php``
* ``app/Config/Cache.php``
* ``app/Config/Feature.php``
* ``app/Config/Generators.php``
* ``app/Config/Kint.php``
* ``app/Config/Publisher.php``
* ``app/Config/Security.php``
* ``app/Views/welcome_message.php``
