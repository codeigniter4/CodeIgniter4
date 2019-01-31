#################
Database Metadata
#################

.. contents::
    :local:
    :depth: 2

**************
Table MetaData
**************

These functions let you fetch table information.

List the Tables in Your Database
================================

**$db->listTables();**

Returns an array containing the names of all the tables in the database
you are currently connected to. Example::

	$tables = $db->listTables();

	foreach ($tables as $table)
	{
		echo $table;
	}

Determine If a Table Exists
===========================

**$db->tableExists();**

Sometimes it's helpful to know whether a particular table exists before
running an operation on it. Returns a boolean TRUE/FALSE. Usage example::

	if ($db->tableExists('table_name'))
	{
		// some code...
	}

.. note:: Replace *table_name* with the name of the table you are looking for.

**************
Field MetaData
**************

List the Fields in a Table
==========================

**$db->getFieldNames()**

Returns an array containing the field names. This query can be called
two ways:

1. You can supply the table name and call it from the $db->
object::

	$fields = $db->getFieldNames('table_name');

	foreach ($fields as $field)
	{
		echo $field;
	}

2. You can gather the field names associated with any query you run by
calling the function from your query result object::

	$query = $db->query('SELECT * FROM some_table');

	foreach ($query->getFieldNames() as $field)
	{
		echo $field;
	}

Determine If a Field is Present in a Table
==========================================

**$db->fieldExists()**

Sometimes it's helpful to know whether a particular field exists before
performing an action. Returns a boolean TRUE/FALSE. Usage example::

	if ($db->fieldExists('field_name', 'table_name'))
	{
		// some code...
	}

.. note:: Replace *field_name* with the name of the column you are looking
	for, and replace *table_name* with the name of the table you are
	looking for.

Retrieve Field Metadata
=======================

**$db->getFieldData()**

Returns an array of objects containing field information.

Sometimes it's helpful to gather the field names or other metadata, like
the column type, max length, etc.

.. note:: Not all databases provide meta-data.

Usage example::

	$fields = $db->getFieldData('table_name');

	foreach ($fields as $field)
	{
		echo $field->name;
		echo $field->type;
		echo $field->max_length;
		echo $field->primary_key;
	}

If you have run a query already you can use the result object instead of
supplying the table name::

	$query  = $db->query("YOUR QUERY");
	$fields = $query->fieldData();

The following data is available from this function if supported by your
database:

-  name - column name
-  max_length - maximum length of the column
-  primary_key - 1 if the column is a primary key
-  type - the type of the column

List the Indexes in a Table
===========================

**$db->getIndexData()**

Returns an array of objects containing index information.

Usage example::

	$keys = $db->getIndexData('table_name');

	foreach ($keys as $key)
	{
		echo $key->name;
		echo $key->type;
		echo $key->fields;  // array of field names
	}

The key types may be unique to the database you are using.
For instance, MySQL will return one of primary, fulltext, spatial, index or unique
for each key associated with a table.
