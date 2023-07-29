##############################
Upgrading from 4.3.x to 4.4.0
##############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

SECURITY
********

When Using $this->validate()
============================

There was a known potential vulnerability in :ref:`$this->validate() <controller-validate>` in the Controller to bypass validation.
The attack could allow developers to misinterpret unvalidated empty data as
validated and proceed with processing.

The :ref:`Validation::getValidated() <validation-getting-validated-data>`
method has been added to ensure that validated data is obtained.

Therefore, when you use ``$this->validate()`` in your Controllers, you should
use the new ``Validation::getValidated()`` method to get the validated data.

.. literalinclude:: ../libraries/validation/045.php
   :lines: 2-

Breaking Changes
****************

.. _upgrade-440-uri-setsegment:

URI::setSegment() Change
========================

Dut to a bug, in previous versions an exception was not thrown if the last segment
``+2`` was specified. This bug has been fixed.

If your code depends on this bug, fix the segment number.

.. literalinclude:: upgrade_440/002.php
   :lines: 2-

When You Extend Exceptions
==========================

If you are extending ``CodeIgniter\Debug\Exceptions`` and have not overridden
the ``exceptionHandler()`` method, defining the new ``Config\Exceptions::handler()``
method in your **app/Config/Exceptions.php** will cause the specified Exception
Handler to be executed.

Your overridden code will no longer be executed, so make any necessary changes
by defining your own exception handler.

See :ref:`custom-exception-handlers` for the detail.

Auto Routing (Improved) and translateURIDashes
==============================================

When using Auto Routing (Improved) and ``$translateURIDashes`` is true
(``$routes->setTranslateURIDashes(true)``), in previous versions due to a bug
two URIs correspond to a single controller method, one URI for dashes
(e.g., **foo-bar**) and one URI for underscores (e.g., **foo_bar**).

This bug was fixed and now URIs for underscores (**foo_bar**) is not accessible.

If you have links to URIs for underscores (**foo_bar**), update them with URIs
for dashes (**foo-bar**).

When Passing Fully Qualified Classnames to Factories
====================================================

The behavior of passing fully qualified classnames to Factories has been changed.
See :ref:`ChangeLog <v440-factories>` for details.

If you have code like ``model('\Myth\Auth\Models\UserModel::class')`` or
``model('Myth\Auth\Models\UserModel')`` (the code may be in the third-party packages),
and you expect to load your ``App\Models\UserModel``, you need to define the
classname to be loaded before the first loading of that class::

    Factories::define('models', 'Myth\Auth\Models\UserModel', 'App\Models\UserModel');

See :ref:`factories-defining-classname-to-be-loaded` for details.

Interface Changes
=================

Some interface changes have been made. Classes that implement them should update
their APIs to reflect the changes. See :ref:`v440-interface-changes` for details.

Method Signature Changes
========================

Some method signature changes have been made. Classes that extend them should
update their APIs to reflect the changes. See :ref:`v440-method-signature-changes`
for details.

Also, the parameter types of some constructors and ``Services::security()`` have changed.
If you call them with the parameters, change the parameter values.
See :ref:`v440-parameter-type-changes` for details.

RouteCollection::$routes
========================

The array structure of the protected property ``$routes`` has been modified for
performance.

If you extend ``RouteCollection`` and use the ``$routes``, update your code to
match the new array structure.

Mandatory File Changes
**********************

Config Files
============

app/Config/App.php
------------------

- The property ``$proxyIPs`` must be an array. If you don't use proxy servers,
  it must be ``public array $proxyIPs = [];``.

.. _upgrade-440-config-routing:

app/Config/Routing.php
----------------------

To clean up the routing system, the following changes were made:

- New **app/Config/Routing.php** file that holds the settings that used to be in the Routes file.
- The **app/Config/Routes.php** file was simplified so that it only contains the routes without settings and verbiage to clutter the file.
- The environment-specific routes files are no longer loaded automatically.

So you need to do:

1. Copy **app/Config/Routing.php** from the new framework to your **app/Config**
   directory, and configure it.
2. Remove all settings in **app/Config/Routes.php** that are no longer needed.
3. If you use the environment-specific routes files, add them to the ``$routeFiles`` property in **app/Config/Routing.php**.

app/Config/Cookie.php
---------------------

The Cookie config items in **app/Config/App.php** are no longer used.

1. Copy **app/Config/Cookie.php** from the new framework to your **app/Config**
   directory, and configure it.
2. Remove the properties (from ``$cookiePrefix`` to ``$cookieSameSite``) in
   **app/Config/App.php**.

app/Config/Security.php
-----------------------

The CSRF config items in **app/Config/App.php** are no longer used.

1. Copy **app/Config/Security.php** from the new framework to your **app/Config**
   directory, and configure it.
2. Remove the properties (from ``$CSRFTokenName`` to ``$CSRFSameSite``) in
   **app/Config/App.php**.

app/Config/Session.php
----------------------

The Session config items in **app/Config/App.php** are no longer used.

1. Copy **app/Config/Session.php** from the new framework to your **app/Config**
   directory, and configure it.
2. Remove the properties (from ``$sessionDriver`` to ``$sessionDBGroup``) in
   **app/Config/App.php**.

Breaking Enhancements
*********************

- **Routing:** The method signature of ``RouteCollection::__construct()`` has been changed.
  The third parameter ``Routing $routing`` has been added. Extending classes
  should likewise add the parameter so as not to break LSP.
- **Validation:** The method signature of ``Validation::check()`` has been changed.
  The ``string`` typehint on the ``$rule`` parameter was removed. Extending classes
  should likewise remove the typehint so as not to break LSP.

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

- app/Config/CURLRequest.php
    - The default value of :ref:`$shareOptions <curlrequest-sharing-options>` has been change to ``false``.
- app/Config/Exceptions.php
    - Added the new method ``handler()`` that define custom Exception Handlers.
      See :ref:`custom-exception-handlers`.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- @TODO
