##################################
Database Quick Start: Example Code
##################################

The following page contains example code showing how the database class
is used. For complete details please read the individual pages
describing each function.

.. contents::
    :local:
    :depth: 2

Initializing the Database Class
===============================

The following code loads and initializes the database class based on
your :doc:`configuration <configuration>` settings:

.. literalinclude:: examples/001.php

Once loaded the class is ready to be used as described below.

.. note:: If all your pages require database access you can connect
    automatically. See the :doc:`connecting <connecting>` page for details.

Standard Query With Multiple Results (Object Version)
=====================================================

.. literalinclude:: examples/002.php

| The above ``getResult()`` function returns an array of **objects**.
| Example: ``$row->title``

Standard Query With Multiple Results (Array Version)
====================================================

.. literalinclude:: examples/003.php

| The above ``getResultArray()`` function returns an array of standard array
  indexes.
| Example: ``$row['title']``

Standard Query With Single Result
=================================

.. literalinclude:: examples/004.php

The above ``getRow()`` function returns an **object**. Example: ``$row->name``

Standard Query With Single Result (Array version)
=================================================

.. literalinclude:: examples/005.php

The above ``getRowArray()`` function returns an **array**. Example:
``$row['name']``.

Standard Insert
===============

.. literalinclude:: examples/006.php

Query Builder Query
===================

The :doc:`Query Builder Pattern <query_builder>` gives you a simplified
means of retrieving data:

.. literalinclude:: examples/007.php

The above ``get()`` function retrieves all the results from the supplied
table. The :doc:`Query Builder <query_builder>` class contains a full
complement of functions for working with data.

Query Builder Insert
====================

.. literalinclude:: examples/008.php

Get table rows in the Command Line
===============================

You can see specific table info on the command line. Assuming there is a table called ``my_table`` , use the following command to get started::

    > php spark db:table my_table

If table `my_table` is not in the database, the CodeIgniter displays a list of available tables to select.
You can also use only the following command without the table name. In this case, the table name will be asked ::

    > php spark db:table

.. note:: You can use the optional ``--desc``, ``--limit-rows``, ``--limit-fields-value`` options at any time when using command ``db:table`` .

Command ``db:table --limit-rows 50``, for example, limits the number of rows to 50 rows.
Command ``db:table --desc``, set the sort direction to "DESC".
And Command ``db:table --limit-fields-value 10`` limits the values of the fields to "10" characters, to prevent confusion of the table output in the terminal.
