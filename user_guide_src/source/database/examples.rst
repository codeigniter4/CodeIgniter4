##################################
Database Quick Start: Example Code
##################################

The following page contains example code showing how the database class
is used. For complete details please read the individual pages
describing each function.

.. note:: CodeIgniter doesn't support dots (``.``) in the database, table, and column names.

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
