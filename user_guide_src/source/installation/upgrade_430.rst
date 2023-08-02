##############################
Upgrading from 4.2.12 to 4.3.0
##############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

Composer Version
****************

.. important:: If you use Composer, CodeIgniter v4.3.0 requires
    Composer 2.0.14 or later.

If you are using older version of Composer, upgrade your ``composer`` tool,
and delete the **vendor/** directory, and run ``composer update`` again.

The procedure, for example, is as follows:

.. code-block:: console

    composer self-update
    rm -rf vendor/
    composer update

Mandatory File Changes
**********************

spark
=====

The following files received significant changes and
**you must merge the updated versions** with your application:

- ``spark``

.. important:: If you do not update this file, Spark commands will not work at all after running ``composer update``.

    The upgrade procedure, for example, is as follows:

    .. code-block:: console

        composer update
        cp vendor/codeigniter4/framework/spark .

Config Files
============

app/Config/Kint.php
-------------------

- **app/Config/Kint.php** has been updated for Kint 5.0.
- You need to replace:

    - ``Kint\Renderer\Renderer`` with ``Kint\Renderer\AbstractRenderer``
    - ``Renderer::SORT_FULL`` with ``AbstractRenderer::SORT_FULL``

app/Config/Exceptions.php
-------------------------

- If you are using PHP 8.2, you need to add new properties ``$logDeprecations`` and ``$deprecationLogLevel``.

Mock Config Classes
-------------------

- If you are using the following Mock Config classes in testing, you need to update the corresponding Config files in **app/Config**:

    - ``MockAppConfig`` (``Config\App``)
    - ``MockCLIConfig`` (``Config\App``)
    - ``MockSecurityConfig`` (``Config\Security``)

- Add **types** to the properties in these Config classes. You may need to fix the property values to match the property types.

composer.json
=============

If you installed CodeIgnter manually, and are using Composer,
you need to remove the following lines, and run ``composer update``.

.. code-block:: text

    {
        ...
        "require": {
            ...
            "kint-php/kint": "^4.2",  <-- Remove this line
            ...
        },
        ...
        "scripts": {
            "post-update-cmd": [
                "CodeIgniter\\ComposerScripts::postUpdate"  <-- Remove this line
            ],
            "test": "phpunit"
        },
        ...
    }

Breaking Changes
****************

Database Exception Changes
==========================

- The exception classes may be changed when database errors occur. If you catch the exceptions,
  you must confirm that your code can catch the exceptions.
- Now a few exceptions will be thrown even if ``CI_DEBUG`` is false.
- During transactions, exceptions are not thrown by default even if ``DBDebug`` is true. If you want
  exceptions to be thrown, you need to call ``transException(true)``.
  See :ref:`transactions-throwing-exceptions`.
- See :ref:`exceptions-when-database-errors-occur` for details.

HTTP Status Code and Exit Code of Uncaught Exceptions
=====================================================

- If you expect *Exception code* as *HTTP status code*, the HTTP status code will be changed.
  In that case, you need to implement ``HTTPExceptionInterface`` in the Exception. See :ref:`error-specify-http-status-code`.
- If you expect *Exit code* based on *Exception code*, the Exit code will be changed.
  In that case, you need to implement ``HasExitCodeInterface`` in the Exception. See :ref:`error-specify-exit-code`.

redirect()->withInput() and Validation Errors
=============================================

``redirect()->withInput()`` and Validation errors had an undocumented behavior.
If you redirect with ``withInput()``, CodeIgniter stores the validation errors
in the session, and you can get the errors in the redirected page from
a validation object *before a new validation is run*::

    // In the controller
    if (! $this->validate($rules)) {
        return redirect()->back()->withInput();
    }

    // In the view of the redirected page
    <?= service('Validation')->listErrors() ?>

This behavior was a bug and fixed in v4.3.0.

If you have code that depends on the bug, you need to change the code.
Use new Form helpers, :php:func:`validation_errors()`, :php:func:`validation_list_errors()` and :php:func:`validation_show_error()` to display Validation Errors,
instead of the Validation object.

Validation Changes
==================

- ``ValidationInterface`` has been changed. Implemented classes should likewise add the methods and the parameters so as not to break LSP. See :ref:`v430-validation-changes` for details.
- The return value of  ``Validation::loadRuleGroup()`` has been changed ``null`` to ``[]`` when the ``$group`` is empty. Update the code if you depend on the behavior.

Time Fixes
==========

- Due to bug fixes, some methods in :doc:`Time <../libraries/time>` have changed from mutable behavior to immutable; ``Time`` now extends ``DateTimeImmutable``. See :ref:`ChangeLog <v430-time-fix>` for details.
- If you need the behavior of ``Time`` before the modification, a compatible ``TimeLegacy`` class has been added. Please replace all ``Time`` with ``TimeLegacy`` in your application code.
- But ``TimeLegacy`` is deprecated. So we recommend you update your code.

E.g.::

    // Before
    $time = Time::now();
    // ...
    if ($time instanceof DateTime) {
        // ...
    }

    // After
    $time = Time::now();
    // ...
    if ($time instanceof DateTimeInterface) {
        // ...
    }

::

    // Before
    $time1 = new Time('2022-10-31 12:00');
    $time2 = $time1->modify('+1 day');
    echo $time1; // 2022-11-01 12:00:00
    echo $time2; // 2022-11-01 12:00:00

    // After
    $time1 = new Time('2022-10-31 12:00');
    $time2 = $time1->modify('+1 day');
    echo $time1; // 2022-10-31 12:00:00
    echo $time2; // 2022-11-01 12:00:00

.. _upgrade-430-stream-filter:

Capturing STDERR and STDOUT streams in Tests
============================================

The way error and output streams are captured has changed. Now instead of::

    use CodeIgniter\Test\Filters\CITestStreamFilter;

    protected function setUp(): void
    {
        CITestStreamFilter::$buffer = '';
        $this->streamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter         = stream_filter_append(STDERR, 'CITestStreamFilter');
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);
    }

need to use::

    use CodeIgniter\Test\Filters\CITestStreamFilter;

    protected function setUp(): void
    {
        CITestStreamFilter::registration();
        CITestStreamFilter::addOutputFilter();
        CITestStreamFilter::addErrorFilter();
    }

    protected function tearDown(): void
    {
        CITestStreamFilter::removeOutputFilter();
        CITestStreamFilter::removeErrorFilter();
    }

Or use the trait ``CodeIgniter\Test\StreamFilterTrait``. See :ref:`testing-cli-output`.

Interface Changes
=================

Some interfaces has been fixed. See :ref:`v430-interface-changes` for details.

Foreign Key Data
================

- The data structure returned by ``BaseConnection::getForeignKeyData()`` has been changed.
  You will need to adjust any code depending on this method to use the new structure.

Example: ``tableprefix_table_column1_column2_foreign``

The data returned has the following structure::

    /**
     * @return array[
     *    {constraint_name} =>
     *        stdClass[
     *            'constraint_name'     => string,
     *            'table_name'          => string,
     *            'column_name'         => string[],
     *            'foreign_table_name'  => string,
     *            'foreign_column_name' => string[],
     *            'on_delete'           => string,
     *            'on_update'           => string,
     *            'match'               => string
     *        ]
     * ]
     */

Breaking Enhancements
*********************

Multiple Domain Support
=======================

- If you set ``Config\App::$allowedHostnames``, URL-related functions such as :php:func:`base_url()`, :php:func:`current_url()`, :php:func:`site_url()` will return the URL with the hostname set in ``Config\App::$allowedHostnames`` if the current URL matches.

Database
========

- The return type of ``CodeIgniter\Database\Database::loadForge()`` has been changed to ``Forge``. Extending classes should likewise change the type.
- The return type of ``CodeIgniter\Database\Database::loadUtils()`` has been changed to ``BaseUtils``. Extending classes should likewise change the type.
- The second parameter ``$index`` of ``BaseBuilder::updateBatch()`` has changed to ``$constraints``. It now accepts types array, string, or ``RawSql``. Extending classes should likewise change types.
- The ``$set`` parameter of ``BaseBuilder::insertBatch()`` and ``BaseBuilder::updateBatch()`` now accepts an object of a single row of data. Extending classes should likewise change the type.
- The third parameter ``$index`` of ``BaseBuilder::_updateBatch()`` has changed to ``$values``, and the parameter type has changed to ``array``. Extending classes should likewise change the type.
- The ``Model::update()`` method now raises a ``DatabaseException`` if it generates an SQL
  statement without a WHERE clause. If you need to update all records in a table, use Query Builder instead. E.g., ``$model->builder()->update($data)``.

.. _upgrade-430-honeypot-and-csp:

Honeypot and CSP
================

When CSP is enabled, id attribute ``id="hpc"`` will be injected into the container tag
for the Honeypot field to hide the field. If the id is already used in your views, you need to change it
with ``Config\Honeypot::$containerId``.
And you can remove ``style="display:none"`` in ``Config\Honeypot::$container``.

Others
======

- **Helper:** Since void HTML elements (e.g. ``<input>``) in ``html_helper``, ``form_helper`` or common functions have been changed to be HTML5-compatible by default and you need to be compatible with XHTML, you must set the ``$html5`` property in **app/Config/DocTypes.php** to ``false``.
- **CLI:** Since the launch of Spark Commands was extracted from ``CodeIgniter\CodeIgniter``, there may be problems running these commands if the ``Services::codeigniter()`` service has been overridden.

Project Files
*************

Numerous files in the **project space** (root, app, public, writable) received updates. Due to
these files being outside of the **system** scope they will not be changed without your intervention.
There are some third-party CodeIgniter modules available to assist with merging changes to
the project space: `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

Content Changes
===============

The following files received significant changes (including deprecations or visual adjustments)
and it is recommended that you merge the updated versions with your application:

.. _upgrade_430_config:

Config
------

- app/Config/App.php
    - The new property ``$allowedHostnames`` is added to set allowed hostnames in the site URL
      other than the hostname in the ``$baseURL``. See :ref:`v430-multiple-domain-support`.
    - The property ``$appTimezone`` has been changed to ``UTC`` to avoid being affected
      by daylight saving time.
- app/Config/Autoload.php
    - The new property ``$helpers`` is added to autoload helpers.
- app/Config/Database.php
    - ``$default['DBDebug']`` and ``$test['DBDebug']`` are changed to ``true`` by default.
      See :ref:`exceptions-when-database-errors-occur`.
- app/Config/DocTypes.php
    - The property ``$html5`` to determine whether to remove the solidus (``/``) character for void HTML
      elements (e.g. ``<input>``) is added, and set to ``true`` by default for HTML5 compatibility.
- app/Config/Encryption.php
    - The new property ``$rawData``,  ``$encryptKeyInfo``, and ``$authKeyInfo`` are added for CI3
      Encryption compatibility. See :ref:`encryption-compatible-with-ci3`.
- app/Config/Exceptions.php
    - Two additional public properties were added: ``$logDeprecations`` and ``$deprecationLogLevel``.
      See See :ref:`logging_deprecation_warnings` for details.
- app/Config/Honeypot.php
    - The new property ``$containerId`` is added to set id attribute value for the container tag
      when CSP is enabled.
    - The ``input`` tag in the property ``$template`` value has been changed to HTML5 compatible.
- app/Config/Logger.php
    - The property ``$threshold`` has been changed to ``9`` in other than ``production``
      environment.
- app/Config/Modules.php
    - The new property ``$composerPackages`` is added to limit Composer package Auto-Discovery for better
      performance.
- app/Config/Routes.php
    - Due to the fact that the approach to running Spark Commands has changed, there is no longer a need
      to load the internal routes of the framework (``SYSTEMPATH . 'Config/Routes.php'``).
- app/Config/Security.php
    - Changed the value of the property ``$redirect`` to ``false`` to prevent redirection when a CSRF
      check fails. This is to make it easier to recognize that it is a CSRF error.
- app/Config/Session.php
    - Added to handle session configuration.
- app/Config/Validation.php
    - The default Validation Rules have been changed to Strict Rules for better security. See :ref:`validation-traditional-and-strict-rules`.

View Files
----------

The following view files have been changed to HTML5 compatible tags.
Also, error messages are now defined in the **Errors** language file.

- app/Views/errors/html/error_404.php
- app/Views/errors/html/error_exception.php
- app/Views/errors/html/production.php
- app/Views/welcome_message.php

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime.
All atomic type properties in ``Config`` classes have been typed:

*   app/Config/App.php
*   app/Config/Autoload.php
*   app/Config/CURLRequest.php
*   app/Config/Cache.php
*   app/Config/ContentSecurityPolicy.php
*   app/Config/Cookie.php
*   app/Config/Database.php
*   app/Config/DocTypes.php
*   app/Config/Email.php
*   app/Config/Encryption.php
*   app/Config/Exceptions.php
*   app/Config/Feature.php
*   app/Config/Filters.php
*   app/Config/Format.php
*   app/Config/Generators.php
*   app/Config/Honeypot.php
*   app/Config/Images.php
*   app/Config/Kint.php
*   app/Config/Logger.php
*   app/Config/Migrations.php
*   app/Config/Mimes.php
*   app/Config/Modules.php
*   app/Config/Pager.php
*   app/Config/Paths.php
*   app/Config/Routes.php
*   app/Config/Security.php
*   app/Config/Session.php
*   app/Config/Toolbar.php
*   app/Config/UserAgents.php
*   app/Config/Validation.php
*   app/Views/errors/html/error_404.php
*   app/Views/errors/html/error_exception.php
*   app/Views/errors/html/production.php
*   app/Views/welcome_message.php
*   composer.json
*   env
*   phpunit.xml.dist
*   spark
