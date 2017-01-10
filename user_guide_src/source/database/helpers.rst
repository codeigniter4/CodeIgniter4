####################
Query Helper Methods
####################

Information From Executing a Query
==================================

**$db->insertID()**

The insert ID number when performing database inserts.

.. note:: If using the PDO driver with PostgreSQL, or using the Interbase
	driver, this function requires a $name parameter, which specifies the
	appropriate sequence to check for the insert id.

**$db->affectedRows()**

Displays the number of affected rows, when doing "write" type queries
(insert, update, etc.).

.. note:: In MySQL "DELETE FROM TABLE" returns 0 affected rows. The database
	class has a small hack that allows it to return the correct number of
	affected rows. By default this hack is enabled but it can be turned off
	in the database driver file.

**$db->getLastQuery()**

Returns a Query object that represents the last query that was run (the query string, not the result).

Information About Your Database
===============================

**$db->countAll()**

Permits you to determine the number of rows in a particular table.
Submit the table name in the first parameter. This is part of Query Builder.
Example::

	echo $db->table('my_table')->countAll();

	// Produces an integer, like 25

**$db->getPlatform()**

Outputs the database platform you are running (MySQL, MS SQL, Postgres,
etc...)::

	echo $db->getPlatform();

**$db->getVersion()**

Outputs the database version you are running::

	echo $db->getVersion();
