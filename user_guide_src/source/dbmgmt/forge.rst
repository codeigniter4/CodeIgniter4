Database Forge Class
####################

The Database Forge Class contains methods that help you manage your
database.

.. contents::
    :local:
    :depth: 2

****************************
Initializing the Forge Class
****************************

.. important:: In order to initialize the Forge class, your database
    driver must already be running, since the Forge class relies on it.

Load the Forge Class as follows:

.. literalinclude:: forge/001.php

You can also pass another database group name to the DB Forge loader, in case
the database you want to manage isn't the default one:

.. literalinclude:: forge/002.php

In the above example, we're passing the name of a different database group
to connect to as the first parameter.

*******************************
Creating and Dropping Databases
*******************************

$forge->createDatabase('db_name')
=================================

Permits you to create the database specified in the first parameter.
Returns true/false based on success or failure:

.. literalinclude:: forge/003.php

An optional second parameter set to true will add ``IF EXISTS`` statement
or will check if a database exists before create it (depending on DBMS).

.. literalinclude:: forge/004.php

$forge->dropDatabase('db_name')
===============================

Permits you to drop the database specified in the first parameter.
Returns true/false based on success or failure:

.. literalinclude:: forge/005.php

Creating Databases in the Command Line
======================================

CodeIgniter supports creating databases straight from your favorite terminal using the dedicated ``db:create``
command. By using this command it is assumed that the database is not yet existing. Otherwise, CodeIgniter
will complain that the database creation has failed.

To start, just type the command and the name of the database (e.g., ``foo``)::

    > php spark db:create foo

If everything went fine, you should expect the ``Database "foo" successfully created.`` message displayed.

If you are on a testing environment or you are using the SQLite3 driver, you may pass in the file extension
for the file where the database will be created using the ``--ext`` option. Valid values are ``db`` and
``sqlite`` and defaults to ``db``. Remember that these should not be preceded by a period.
::

    > php spark db:create foo --ext sqlite
    // will create the db file in WRITEPATH/foo.sqlite

.. note:: When using the special SQLite3 database name ``:memory:``, expect that the command will still
    produce a success message but no database file is created. This is because SQLite3 will just use
    an in-memory database.

****************************
Creating and Dropping Tables
****************************

There are several things you may wish to do when creating tables. Add
fields, add keys to the table, alter columns. CodeIgniter provides a
mechanism for this.

Adding Fields
=============

Fields are normally created via an associative array. Within the array, you must
include a ``type`` key that relates to the datatype of the field. For
example, INT, VARCHAR, TEXT, etc. Many datatypes (for example VARCHAR)
also require a ``constraint`` key.

.. literalinclude:: forge/006.php

Additionally, the following key/values can be used:

-  ``unsigned``/true : to generate "UNSIGNED" in the field definition.
-  ``default``/value : to generate a default value in the field definition.
-  ``null``/true : to generate "null" in the field definition. Without this,
   the field will default to "NOT null".
-  ``auto_increment``/true : generates an auto_increment flag on the
   field. Note that the field type must be a type that supports this,
   such as integer.
-  ``unique``/true : to generate a unique key for the field definition.

.. literalinclude:: forge/007.php

After the fields have been defined, they can be added using
``$forge->addField($fields)`` followed by a call to the
``createTable()`` method.

$forge->addField()
------------------

The add fields method will accept the above array.

.. _forge-addfield-default-value-rawsql:

Raw Sql Strings as Default Values
---------------------------------

Since v4.2.0, ``$forge->addField()`` accepts a ``CodeIgniter\Database\RawSql`` instance, which expresses raw SQL strings.


.. literalinclude:: forge/027.php

.. warning:: When you use ``RawSql``, you MUST escape the data manually. Failure to do so could result in SQL injections.

Passing Strings as Fields
-------------------------

If you know exactly how you want a field to be created, you can pass the
string into the field definitions with ``addField()``:

.. literalinclude:: forge/008.php

.. note:: Passing raw strings as fields cannot be followed by ``addKey()`` calls on those fields.

.. note:: Multiple calls to ``addField()`` are cumulative.

Creating an id field
--------------------

There is a special exception for creating id fields. A field with type
id will automatically be assigned as an INT(9) auto_incrementing
Primary Key.

.. literalinclude:: forge/009.php

Adding Keys
===========

Generally speaking, you'll want your table to have Keys. This is
accomplished with ``$forge->addKey('field')``. The optional second
parameter set to true will make it a primary key and the third
parameter set to true will make it a unique key. Note that ``addKey()``
must be followed by a call to ``createTable()``.

Multiple column non-primary keys must be sent as an array. Sample output
below is for MySQL.

.. literalinclude:: forge/010.php

To make code reading more objective it is also possible to add primary
and unique keys with specific methods:

.. literalinclude:: forge/011.php

.. _adding-foreign-keys:

Adding Foreign Keys
===================

Foreign Keys help to enforce relationships and actions across your tables. For tables that support Foreign Keys,
you may add them directly in forge:

.. literalinclude:: forge/012.php

You can specify the desired action for the "on delete" and "on update" properties of the constraint:

.. literalinclude:: forge/013.php

Creating a Table
================

After fields and keys have been declared, you can create a new table
with

.. literalinclude:: forge/014.php

An optional second parameter set to true will create the table only if it doesn't already exist.

.. literalinclude:: forge/015.php

You could also pass optional table attributes, such as MySQL's ``ENGINE``:

.. literalinclude:: forge/016.php

.. note:: Unless you specify the ``CHARACTER SET`` and/or ``COLLATE`` attributes,
    ``createTable()`` will always add them with your configured *charset*
    and *DBCollat* values, as long as they are not empty (MySQL only).

Dropping a Table
================

Execute a ``DROP TABLE`` statement and optionally add an ``IF EXISTS`` clause.

.. literalinclude:: forge/017.php

A third parameter can be passed to add a ``CASCADE`` option, which might be required for some
drivers to handle removal of tables with foreign keys.

.. literalinclude:: forge/018.php

Dropping a Foreign Key
======================

Execute a DROP FOREIGN KEY.

.. literalinclude:: forge/019.php

Dropping a Key
======================

Execute a DROP KEY.

.. literalinclude:: forge/020.php

Renaming a Table
================

Executes a TABLE rename

.. literalinclude:: forge/021.php

****************
Modifying Tables
****************

Adding a Column to a Table
==========================

$forge->addColumn()
-------------------

The ``addColumn()`` method is used to modify an existing table. It
accepts the same field array as above, and can be used for an unlimited
number of additional fields.

.. literalinclude:: forge/022.php

If you are using MySQL or CUBIRD, then you can take advantage of their
``AFTER`` and ``FIRST`` clauses to position the new column.

Examples:

.. literalinclude:: forge/023.php

Dropping Columns From a Table
==============================

$forge->dropColumn()
--------------------

Used to remove a column from a table.

.. literalinclude:: forge/024.php

Used to remove multiple columns from a table.

.. literalinclude:: forge/025.php

Modifying a Column in a Table
=============================

$forge->modifyColumn()
----------------------

The usage of this method is identical to ``addColumn()``, except it
alters an existing column rather than adding a new one. In order to
change the name, you can add a "name" key into the field defining array.

.. literalinclude:: forge/026.php

***************
Class Reference
***************

.. php:namespace:: CodeIgniter\Database

.. php:class:: Forge

    .. php:method:: addColumn($table[, $field = []])

        :param    string    $table: Table name to add the column to
        :param    array    $field: Column definition(s)
        :returns:    true on success, false on failure
        :rtype:    bool

        Adds a column to a table. Usage:  See `Adding a Column to a Table`_.

    .. php:method:: addField($field)

        :param    array    $field: Field definition to add
        :returns:    \CodeIgniter\Database\Forge instance (method chaining)
        :rtype:    \CodeIgniter\Database\Forge

                Adds a field to the set that will be used to create a table. Usage:  See `Adding Fields`_.

    .. php:method:: addForeignKey($fieldName, $tableName, $tableField[, $onUpdate = '', $onDelete = ''])

        :param    string|string[]    $fieldName: Name of a key field or an array of fields
        :param    string    $tableName: Name of a parent table
        :param    string|string[]    $tableField: Name of a parent table field or an array of fields
        :param    string    $onUpdate: Desired action for the "on update"
        :param    string    $onDelete: Desired action for the "on delete"
        :returns:    \CodeIgniter\Database\Forge instance (method chaining)
        :rtype:    \CodeIgniter\Database\Forge

        Adds a foreign key to the set that will be used to create a table. Usage:  See `Adding Foreign Keys`_.

    .. php:method:: addKey($key[, $primary = false[, $unique = false]])

        :param    mixed    $key: Name of a key field or an array of fields
        :param    bool    $primary: Set to true if it should be a primary key or a regular one
        :param    bool    $unique: Set to true if it should be a unique key or a regular one
        :returns:    \CodeIgniter\Database\Forge instance (method chaining)
        :rtype:    \CodeIgniter\Database\Forge

        Adds a key to the set that will be used to create a table. Usage:  See `Adding Keys`_.

    .. php:method:: addPrimaryKey($key)

        :param    mixed    $key: Name of a key field or an array of fields
        :returns:    \CodeIgniter\Database\Forge instance (method chaining)
        :rtype:    \CodeIgniter\Database\Forge

        Adds a primary key to the set that will be used to create a table. Usage:  See `Adding Keys`_.

    .. php:method:: addUniqueKey($key)

        :param    mixed    $key: Name of a key field or an array of fields
        :returns:    \CodeIgniter\Database\Forge instance (method chaining)
        :rtype:    \CodeIgniter\Database\Forge

        Adds a unique key to the set that will be used to create a table. Usage:  See `Adding Keys`_.

    .. php:method:: createDatabase($dbName[, $ifNotExists = false])

        :param    string    $db_name: Name of the database to create
        :param    string    $ifNotExists: Set to true to add an ``IF NOT EXISTS`` clause or check if database exists
        :returns:    true on success, false on failure
        :rtype:    bool

        Creates a new database. Usage:  See `Creating and Dropping Databases`_.

    .. php:method:: createTable($table[, $if_not_exists = false[, array $attributes = []]])

        :param    string    $table: Name of the table to create
        :param    string    $if_not_exists: Set to true to add an ``IF NOT EXISTS`` clause
        :param    string    $attributes: An associative array of table attributes
        :returns:  Query object on success, false on failure
        :rtype:    mixed

        Creates a new table. Usage:  See `Creating a Table`_.

    .. php:method:: dropColumn($table, $column_name)

        :param    string    $table: Table name
        :param    mixed    $column_names: Comma-delimited string or an array of column names
        :returns:    true on success, false on failure
        :rtype:    bool

        Drops single or multiple columns from a table. Usage:  See `Dropping Columns From a Table`_.

    .. php:method:: dropDatabase($dbName)

        :param    string    $dbName: Name of the database to drop
        :returns:    true on success, false on failure
        :rtype:    bool

        Drops a database. Usage:  See `Creating and Dropping Databases`_.

    .. php:method:: dropTable($table_name[, $if_exists = false])

        :param    string    $table: Name of the table to drop
        :param    string    $if_exists: Set to true to add an ``IF EXISTS`` clause
        :returns:    true on success, false on failure
        :rtype:    bool

        Drops a table. Usage:  See `Dropping a Table`_.

    .. php:method:: modifyColumn($table, $field)

        :param    string    $table: Table name
        :param    array    $field: Column definition(s)
        :returns:    true on success, false on failure
        :rtype:    bool

        Modifies a table column. Usage:  See `Modifying a Column in a Table`_.

    .. php:method:: renameTable($table_name, $new_table_name)

        :param    string    $table: Current of the table
        :param    string    $new_table_name: New name of the table
        :returns:  Query object on success, false on failure
        :rtype:    mixed

        Renames a table. Usage:  See `Renaming a Table`_.
