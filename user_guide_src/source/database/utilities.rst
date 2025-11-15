##################
Database Utilities
##################

The Database Utility Class contains methods that help you manage your database.

.. contents::
    :local:
    :depth: 2

******************************
Initializing the Utility Class
******************************

Load the Utility Class as follows:

.. literalinclude:: utilities/002.php
    :lines: 2-

You can also pass another database group to the DB Utility loader, in case
the database you want to manage isn't the default one:

.. literalinclude:: utilities/003.php
    :lines: 2-

In the above example, we're passing a database group name as the first
parameter.

****************************
Using the Database Utilities
****************************

Retrieve List of Database Names
================================

Returns an array of database names:

.. literalinclude:: utilities/004.php
    :lines: 2-

Determine If a Database Exists
==============================

Sometimes it's helpful to know whether a particular database exists.
Returns a boolean ``true``/``false``. Usage example:

.. literalinclude:: utilities/005.php
    :lines: 2-

.. note:: Replace ``database_name`` with the name of the database you are
    looking for. This method is case sensitive.

Optimize a Table
================

Permits you to optimize a table using the table name specified in the
first parameter. Returns ``true``/``false`` based on success or failure:

.. literalinclude:: utilities/006.php
    :lines: 2-

.. note:: Not all database platforms support table optimization. It is
    mostly for use with MySQL.

Optimize a Database
===================

Permits you to optimize the database your DB class is currently
connected to. Returns an array containing the DB status messages or
``false`` on failure:

.. literalinclude:: utilities/008.php
    :lines: 2-

.. note:: Not all database platforms support database optimization. It
    it is mostly for use with MySQL.

Export a Query Result as a CSV File
===================================

Permits you to generate a CSV file from a query result. The first
parameter of the method must contain the result object from your
query. Example:

.. literalinclude:: utilities/009.php
    :lines: 2-

The second, third, and fourth parameters allow you to set the delimiter
newline, and enclosure characters respectively. By default commas are
used as the delimiter, ``"\n"`` is used as a new line, and a double-quote
is used as the enclosure. Example:

.. literalinclude:: utilities/010.php
    :lines: 2-

.. important:: This method will NOT write the CSV file for you. It
    simply creates the CSV layout. If you need to write the file
    use the :php:func:`write_file()` helper.

Export a Query Result as an XML Document
========================================

Permits you to generate an XML file from a query result. The first
parameter expects a query result object, the second may contain an
optional array of config parameters. Example:

.. literalinclude:: utilities/001.php

and it will get the following xml result when the ``mytable`` has columns ``id`` and ``name``::

    <root>
        <element>
            <id>1</id>
            <name>bar</name>
        </element>
    </root>

.. important:: This method will NOT write the XML file for you. It
    simply creates the XML layout. If you need to write the file
    use the :php:func:`write_file()` helper.
