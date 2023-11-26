##############################
Upgrading from 4.3.8 to 4.4.0
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

Site URI Changes
================

- Because of the rework for the current URI determination, the framework may return
  site URIs or the URI paths slightly differently than in previous versions. It may
  break your test code. Update assertions if the existing tests fail.
- When your baseURL has sub-directories and you get the relative path to baseURL of
  the current URI by the ``URI::getPath()`` method, you must use the new
  ``SiteURI::getRoutePath()`` method instead.

See :ref:`v440-site-uri-changes` for details.

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

When Passing Classname with Namespace to Factories
==================================================

The behavior of passing a classname with a namespace to Factories has been changed.
See :ref:`ChangeLog <v440-factories>` for details.

If you have code like ``model(\Myth\Auth\Models\UserModel::class)`` or
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

index.php and spark
===================

The following files received significant changes and
**you must merge the updated versions** with your application:

- ``public/index.php`` (see also :ref:`v440-codeigniter-and-exit`)
- ``spark``

.. important:: If you don't update the above files, CodeIgniter will not work
    properly after running ``composer update``.

    The upgrade procedure, for example, is as follows:

    .. code-block:: console

        composer update
        cp vendor/codeigniter4/framework/public/index.php public/index.php
        cp vendor/codeigniter4/framework/spark spark

Config Files
============

app/Config/App.php
------------------

The property ``$proxyIPs`` must be an array. If you don't use proxy servers,
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

app/Config/Toolbar.php
----------------------

You need to add the new properties ``$watchedDirectories`` and ``$watchedExtensions``
for :ref:`debug-toolbar-hot-reload`::

    --- a/app/Config/Toolbar.php
    +++ b/app/Config/Toolbar.php
    @@ -88,4 +88,31 @@ class Toolbar extends BaseConfig
          * `$maxQueries` defines the maximum amount of queries that will be stored.
          */
         public int $maxQueries = 100;
    +
    +    /**
    +     * --------------------------------------------------------------------------
    +     * Watched Directories
    +     * --------------------------------------------------------------------------
    +     *
    +     * Contains an array of directories that will be watched for changes and
    +     * used to determine if the hot-reload feature should reload the page or not.
    +     * We restrict the values to keep performance as high as possible.
    +     *
    +     * NOTE: The ROOTPATH will be prepended to all values.
    +     */
    +    public array $watchedDirectories = [
    +        'app',
    +    ];
    +
    +    /**
    +     * --------------------------------------------------------------------------
    +     * Watched File Extensions
    +     * --------------------------------------------------------------------------
    +     *
    +     * Contains an array of file extensions that will be watched for changes and
    +     * used to determine if the hot-reload feature should reload the page or not.
    +     */
    +    public array $watchedExtensions = [
    +        'php', 'css', 'js', 'html', 'svg', 'json', 'env',
    +    ];
     }


app/Config/Events.php
---------------------

You need to add the code to add a route for :ref:`debug-toolbar-hot-reload`::

    --- a/app/Config/Events.php
    +++ b/app/Config/Events.php
    @@ -4,6 +4,7 @@ namespace Config;

     use CodeIgniter\Events\Events;
     use CodeIgniter\Exceptions\FrameworkException;
    +use CodeIgniter\HotReloader\HotReloader;

     /*
      * --------------------------------------------------------------------
    @@ -44,5 +45,11 @@ Events::on('pre_system', static function () {
         if (CI_DEBUG && ! is_cli()) {
             Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
             Services::toolbar()->respond();
    +        // Hot Reload route - for framework use on the hot reloader.
    +        if (ENVIRONMENT === 'development') {
    +            Services::routes()->get('__hot-reload', static function () {
    +                (new HotReloader())->run();
    +            });
    +        }
         }
     });

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

- app/Config/App.php
- app/Config/CURLRequest.php
- app/Config/Cookie.php
- app/Config/Database.php
- app/Config/Events.php
- app/Config/Exceptions.php
- app/Config/Filters.php
- app/Config/Routes.php
- app/Config/Routing.php
- app/Config/Toolbar.php
- public/index.php
- spark
