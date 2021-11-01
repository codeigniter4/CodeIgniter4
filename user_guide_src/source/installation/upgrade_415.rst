#############################
Upgrading from 4.1.4 to 4.1.5
#############################

.. contents::
    :local:
    :depth: 1

Breaking Changes
================

Changes for set() method in BaseBuilder and Model class
-------------------------------------------------------

The casting for the ``$value`` parameter has been removed to fix a bug where passing parameters as array and string
to the ``set()`` method were handled differently. If you extended the ``BaseBuilder`` class or ``Model`` class yourself
and modified the ``set()`` method, then you need to change its definition from
``public function set($key, ?string $value = '', ?bool $escape = null)`` to
``public function set($key, $value = '', ?bool $escape = null)``.

Session DatabaseHandler's database table change
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

If you want the same behavior as the previous version, set the CSRF filter like the following in **app/Config/Filters.php**::

    public $methods = [
        'get'  => ['csrf'],
        'post' => ['csrf'],
    ];

Protecting **GET** method needs only when you use ``form_open()`` auto-generation of CSRF field.

CURLRequest header change
-------------------------

In the previous version, if you didn't provide your own headers, ``CURLRequest`` would send the request-headers from the browser.
The bug was fixed. If your requests depend on the headers, your requests might fail after upgrading.
In this case, add the necessary headers manually.
See `CURLRequest Class <../libraries/curlrequest.html#headers>`_ for how to add.

Query Builder changes
---------------------

For optimization and a bug fix, the following behaviors, mostly used in testing, have been changed.

- When you use ``insertBatch()`` and ``updateBatch()``, the return value of ``$query->getOriginalQuery()`` changed.
- If ``testMode`` is ``true``, ``insertBatch()`` will return an SQL string array instead of the number of affected rows that were wrong.

Breaking Enhancements
=====================

Multiple filters for a route
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

See *Applying Filters* in :doc:`Routing </incoming/routing>` for the functionality.

Project Files
=============

Content Changes
---------------

All Changes
-----------
