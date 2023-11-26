#################
Database Commands
#################

CodeIgniter provides some simple commands for database management.

.. contents::
    :local:
    :depth: 2

*************************
Showing Table Information
*************************

List the Tables in Your Database
================================

db:table --show
---------------

To list all the tables in your database straight from your favorite terminal,
you can use the ``db:table --show`` command:

.. code-block:: console

    php spark db:table --show

When using this command it is assumed that a table exists.
Otherwise, CodeIgniter will complain that the database has no tables.

Retrieve Some Records
=====================

db:table
--------

When you have a table named ``my_table``, you can see the field names and the records of a table:

.. code-block:: console

    php spark db:table my_table

If the table ``my_table`` is not in the database, CodeIgniter displays a list of available tables to select.

You can also use the following command without the table name:

.. code-block:: console

    php spark db:table

In this case, the table name will be asked.

You can also pass a few options:

.. code-block:: console

    php spark db:table my_table --limit-rows 50 --limit-field-value 20 --desc

The option ``--limit-rows 50`` limits the number of rows to 50 rows.

The option  ``--limit-field-value 20`` limits the length of the field values to 20 characters, to prevent confusion of the table output in the terminal.

The option ``--desc`` sets the sort direction to "DESC".

Retrieve Field Metadata
=======================

db:table --metadata
-------------------

When you have a table named ``my_table``, you can see metadata like the column type, max length of the table with the ``--metadata`` option:

.. code-block:: console

    php spark db:table my_table --metadata

When using this command it is assumed that the table exists.
Otherwise, CodeIgniter will show a table list to select.
Also, you can use this command as ``db:table --metadata``.
