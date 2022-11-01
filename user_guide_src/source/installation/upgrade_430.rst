#############################
Upgrading from 4.2.1 to 4.3.0
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

spark
=====

The following files received significant changes and
**you must merge the updated versions** with your application:

* ``spark``

.. important:: If you do not update this file, Spark commands will not work at all after running ``composer update``.

    The upgrade procedure, for example, is as follows::

        > composer update
        > cp vendor/codeigniter4/framework/spark .

Config Files
============

If you are using the following Mock Config classes in testing, you need to update the corresponding Config files in **app/Config**:

- ``MockAppConfig`` (``Config\App``)
- ``MockCLIConfig`` (``Config\App``)
- ``MockSecurityConfig`` (``Config\Security``)

Add **types** to the properties in these Config classes. You may need to fix the property values to match the property types.

Breaking Changes
****************

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

.. _upgrade-430-stream-filter:

Capturing STDERR and STDOUT streams in tests
============================================

The way error and output streams are captured has changed. Now instead of::

    use CodeIgniter\Test\Filters\CITestStreamFilter;

    protected function setUp(): void
    {
        CITestStreamFilter::$buffer = '';
        $this->stream_filter        = stream_filter_append(STDOUT, 'CITestStreamFilter');
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->stream_filter);
    }

need to use::

    use CodeIgniter\Test\Filters\CITestStreamFilter;

    protected function setUp(): void
    {
        CITestStreamFilter::registration();
        CITestStreamFilter::addOutputFilter();
    }

    protected function tearDown(): void
    {
        CITestStreamFilter::removeOutputFilter();
    }

Or use the trait ``CodeIgniter\Test\StreamFilterTrait``. See :ref:`testing-cli-output`.

Interface Changes
=================

Some interfaces has been fixed. See :ref:`v430-interface-changes` for details.

Foreign Key Data
=====================================================

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

Others
======

- The exception classes may be changed when database errors occur. If you catch the exceptions, you must confirm that your code can catch the exceptions. See :ref:`exceptions-when-database-errors-occur` for details.

Breaking Enhancements
*********************

- Since void HTML elements (e.g. ``<input>``) in ``html_helper``, ``form_helper`` or common functions have been changed to be HTML5-compatible by default and you need to be compatible with XHTML, you must set the ``$html5`` property in **app/Config/DocTypes.php** to ``false``.
- Since the launch of Spark Commands was extracted from ``CodeIgniter\CodeIgniter``, there may be problems running these commands if the ``Services::codeigniter()`` service has been overridden.
- The return type of ``CodeIgniter\Database\Database::loadForge()`` has been changed to ``Forge``. Extending classes should likewise change the type.
- The return type of ``CodeIgniter\Database\Database::loadUtils()`` has been changed to ``BaseUtils``. Extending classes should likewise change the type.
- The second parameter ``$index`` of ``BaseBuilder::updateBatch()`` has changed to ``$constraints``. It now accepts types array, string, or ``RawSql``. Extending classes should likewise change types.
- The ``$set`` parameter of ``BaseBuilder::insertBatch()`` and ``BaseBuilder::updateBatch()`` now accepts an object of a single row of data. Extending classes should likewise change the type.
- The third parameter ``$index`` of ``BaseBuilder::_updateBatch()`` has changed to ``$values``, and the parameter type has changed to ``array``. Extending classes should likewise change the type.

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

* ``app/Config/DocTypes.php``
    * The property ``$html5`` to determine whether to remove the solidus (``/``) character for void HTML elements (e.g. ``<input>``) is added, and set to ``true`` by default for HTML5 compatibility.
* ``app/Config/Exceptions.php``
    * Two additional public properties were added: ``$logDeprecations`` and ``$deprecationLogLevel``.
* ``app/Config/Routes.php``
    * Due to the fact that the approach to running Spark Commands has changed, there is no longer a need to load the internal routes of the framework.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

* app/Config/DocTypes.php
* app/Config/Exceptions.php
* app/Config/Routes.php
* spark
