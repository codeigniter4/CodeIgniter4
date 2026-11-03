####################
Query Helper Methods
####################

.. contents::
    :local:
    :depth: 2

Information From Executing a Query
**********************************

$db->insertID()
===============

The insert ID number when performing database inserts.

$db->affectedRows()
===================

Displays the number of affected rows, when doing "write" type queries (insert, update, etc.).

.. note:: In MySQL, "DELETE FROM TABLE" returns 0 affected rows. The database class has a small hack that allows it to
    return the correct number of affected rows. By default, this hack is enabled, but it can be turned off in the
    database driver file.

$db->getLastQuery()
===================

Returns a Query object that represents the last query that was run (the query string, not the result).

Information About Your Database
*******************************

$builder->countAll()
====================

Permits you to determine the number of rows in a particular table. Select the table with ``$db->table()`` first. The
optional boolean parameter controls whether to reset the Query Builder state.

.. literalinclude:: helpers/001.php

$builder->countAllResults()
===========================

Permits you to determine the number of rows in a particular result set. Select the table with ``$db->table()`` first.
The optional boolean parameter controls whether to reset the Query Builder state.

.. literalinclude:: helpers/002.php

$db->getPlatform()
==================

Outputs the database platform (DBDriver) you are running (MySQLi, SQLSRV, Postgre, etc.):

.. literalinclude:: helpers/003.php

$db->getVersion()
=================

Outputs the database version you are running:

.. literalinclude:: helpers/004.php
