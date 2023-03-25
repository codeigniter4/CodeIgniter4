########################
Generating Query Results
########################

There are several ways to generate query results:

.. contents::
    :local:
    :depth: 3

*************
Result Arrays
*************

getResult()
===========

This method returns the query result as an array of **objects**, or
**an empty array** on failure.

Getting an Array of stdClass
----------------------------

Typically you'll use this in a foreach
loop, like this:

.. literalinclude:: results/001.php

The above method is an alias of :php:meth:`CodeIgniter\\Database\\BaseResult::getResultObject()`.

Getting an Array of Array
-------------------------

You can pass in the string 'array' if you wish to get your results
as an array of arrays:

.. literalinclude:: results/002.php

The above usage is an alias of `getResultArray()`_.

Getting an Array of Custom Object
---------------------------------

You can also pass a string to ``getResult()`` which represents a class to
instantiate for each result object

.. literalinclude:: results/003.php

The above method is an alias of `getCustomResultObject()`_.

getResultArray()
================

This method returns the query result as a pure array, or an empty
array when no result is produced. Typically you'll use this in a foreach
loop, like this:

.. literalinclude:: results/004.php

***********
Result Rows
***********

getRow()
========

This method returns a single result row. If your query has more than
one row, it returns only the first row. The result is returned as an
**object**. Here's a usage example:

.. literalinclude:: results/005.php

If you want a specific row returned you can submit the row number as a
digit in the first parameter:

.. literalinclude:: results/006.php

You can also add a second String parameter, which is the name of a class
to instantiate the row with:

.. literalinclude:: results/007.php

getRowArray()
=============

Identical to the above ``row()`` method, except it returns an array.
Example:

.. literalinclude:: results/008.php

If you want a specific row returned you can submit the row number as a
digit in the first parameter:

.. literalinclude:: results/009.php

In addition, you can walk forward/backwards/first/last through your
results using these variations:

    | ``$row = $query->getFirstRow()``
    | ``$row = $query->getLastRow()``
    | ``$row = $query->getNextRow()``
    | ``$row = $query->getPreviousRow()``

By default they return an object unless you put the word "array" in the
parameter:

    | ``$row = $query->getFirstRow('array')``
    | ``$row = $query->getLastRow('array')``
    | ``$row = $query->getNextRow('array')``
    | ``$row = $query->getPreviousRow('array')``

.. note:: All the methods above will load the whole result into memory
    (prefetching). Use ``getUnbufferedRow()`` for processing large
    result sets.

getUnbufferedRow()
==================

This method returns a single result row without prefetching the whole
result in memory as ``row()`` does. If your query has more than one row,
it returns the current row and moves the internal data pointer ahead.

.. literalinclude:: results/010.php

For use with MySQLi you may set MySQLi's result mode to
``MYSQLI_USE_RESULT`` for maximum memory savings. Use of this is not
generally recommended but it can be beneficial in some circumstances
such as writing large queries to csv. If you change the result mode
be aware of the tradeoffs associated with it.

.. literalinclude:: results/011.php

.. note:: When using ``MYSQLI_USE_RESULT`` all subsequent calls on the same
    connection will result in error until all records have been fetched or
    a ``freeResult()`` call has been made. The ``getNumRows()`` method will only
    return the number of rows based on the current position of the data pointer.
    MyISAM tables will remain locked until all the records have been fetched
    or a ``freeResult()`` call has been made.

You can optionally pass 'object' (default) or 'array' in order to specify
the returned value's type:

.. literalinclude:: results/012.php

*********************
Custom Result Objects
*********************

You can have the results returned as an instance of a custom class instead
of a ``stdClass`` or array, as the ``getResult()`` and ``getResultArray()``
methods allow. If the class is not already loaded into memory, the Autoloader
will attempt to load it. The object will have all values returned from the
database set as properties. If these have been declared and are non-public
then you should provide a ``__set()`` method to allow them to be set.

Example:

.. literalinclude:: results/013.php

In addition to the two methods listed below, the following methods also can
take a class name to return the results as: ``getFirstRow()``, ``getLastRow()``,
``getNextRow()``, and ``getPreviousRow()``.

getCustomResultObject()
=======================

Returns the entire result set as an array of instances of the class requested.
The only parameter is the name of the class to instantiate.

Example:

.. literalinclude:: results/014.php

getCustomRowObject()
====================

Returns a single row from your query results. The first parameter is the row
number of the results. The second parameter is the class name to instantiate.

Example:

.. literalinclude:: results/015.php

You can also use the ``getRow()`` method in exactly the same way.

Example:

.. literalinclude:: results/016.php

*********************
Result Helper Methods
*********************

getFieldCount()
===============

The number of FIELDS (columns) returned by the query. Make sure to call
the method using your query result object:

.. literalinclude:: results/017.php

getFieldNames()
===============

Returns an array with the names of the FIELDS (columns) returned by the query.
Make sure to call the method using your query result object:

.. literalinclude:: results/018.php

getNumRows()
============

The number of records returned by the query. Make sure to call
the method using your query result object:

.. literalinclude:: results/019.php

.. note:: Because SQLite3 lacks an efficient method returning a record count,
    CodeIgniter will fetch and buffer the query result records internally and
    return a count of the resulting record array, which can be inefficient.

freeResult()
============

It frees the memory associated with the result and deletes the result
resource ID. Normally PHP frees its memory automatically at the end of
script execution. However, if you are running a lot of queries in a
particular script you might want to free the result after each query
result has been generated in order to cut down on memory consumption.

Example:

.. literalinclude:: results/020.php

dataSeek()
==========

This method sets the internal pointer for the next result row to be
fetched. It is only useful in combination with ``getUnbufferedRow()``.

It accepts a positive integer value, which defaults to 0 and returns
true on success or false on failure.

.. literalinclude:: results/021.php

.. note:: Not all database drivers support this feature and will return false.
    Most notably - you won't be able to use it with PDO.

***************
Class Reference
***************

.. php:namespace:: CodeIgniter\Database

.. php:class:: BaseResult

    .. php:method:: getResult([$type = 'object'])

        :param    string    $type: Type of requested results - array, object, or class name
        :returns:    Array containing the fetched rows
        :rtype:    array

        A wrapper for the ``getResultArray()``, ``getResultObject()``
        and ``getCustomResultObject()`` methods.

        Usage: see `Result Arrays`_.

    .. php:method:: getResultArray()

        :returns:    Array containing the fetched rows
        :rtype:    array

        Returns the query results as an array of rows, where each
        row is itself an associative array.

        Usage: see `Result Arrays`_.

    .. php:method:: getResultObject()

        :returns:    Array containing the fetched rows
        :rtype:    array

        Returns the query results as an array of rows, where each
        row is an object of type ``stdClass``.

        Usage: see `Getting an Array of stdClass`_.

    .. php:method:: getCustomResultObject($class_name)

        :param    string    $class_name: Class name for the resulting rows
        :returns:    Array containing the fetched rows
        :rtype:    array

        Returns the query results as an array of rows, where each
        row is an instance of the specified class.

    .. php:method:: getRow([$n = 0[, $type = 'object']])

        :param    int    $n: Index of the query results row to be returned
        :param    string    $type: Type of the requested result - array, object, or class name
        :returns:    The requested row or null if it doesn't exist
        :rtype:    mixed

        A wrapper for the ``getRowArray()``, ``getRowObject()`` and
        ``getCustomRowObject()`` methods.

        Usage: see `Result Rows`_.

    .. php:method:: getUnbufferedRow([$type = 'object'])

        :param    string    $type: Type of the requested result - array, object, or class name
        :returns:    Next row from the result set or null if it doesn't exist
        :rtype:    mixed

        Fetches the next result row and returns it in the
        requested form.

        Usage: see `Result Rows`_.

    .. php:method:: getRowArray([$n = 0])

        :param    int    $n: Index of the query results row to be returned
        :returns:    The requested row or null if it doesn't exist
        :rtype:    array

        Returns the requested result row as an associative array.

        Usage: see `Result Rows`_.

    .. php:method:: getRowObject([$n = 0])

        :param    int    $n: Index of the query results row to be returned
                :returns:    The requested row or null if it doesn't exist
        :rtype:    stdClass

        Returns the requested result row as an object of type
        ``stdClass``.

        Usage: see `Result Rows`_.

    .. php:method:: getCustomRowObject($n, $type)

        :param    int    $n: Index of the results row to return
        :param    string    $class_name: Class name for the resulting row
        :returns:    The requested row or null if it doesn't exist
        :rtype:    $type

        Returns the requested result row as an instance of the
        requested class.

    .. php:method:: dataSeek([$n = 0])

        :param    int    $n: Index of the results row to be returned next
        :returns:    true on success, false on failure
        :rtype:    bool

        Moves the internal results row pointer to the desired offset.

        Usage: see `Result Helper Methods`_.

    .. php:method:: setRow($key[, $value = null])

        :param    mixed    $key: Column name or array of key/value pairs
        :param    mixed    $value: Value to assign to the column, $key is a single field name
        :rtype:    void

        Assigns a value to a particular column.

    .. php:method:: getNextRow([$type = 'object'])

        :param    string    $type: Type of the requested result - array, object, or class name
        :returns:    Next row of result set, or null if it doesn't exist
        :rtype:    mixed

        Returns the next row from the result set.

    .. php:method:: getPreviousRow([$type = 'object'])

        :param    string    $type: Type of the requested result - array, object, or class name
        :returns:    Previous row of result set, or null if it doesn't exist
        :rtype:    mixed

        Returns the previous row from the result set.

    .. php:method:: getFirstRow([$type = 'object'])

        :param    string    $type: Type of the requested result - array, object, or class name
        :returns:    First row of result set, or null if it doesn't exist
        :rtype:    mixed

        Returns the first row from the result set.

    .. php:method:: getLastRow([$type = 'object'])

        :param    string    $type: Type of the requested result - array, object, or class name
        :returns:    Last row of result set, or null if it doesn't exist
        :rtype:    mixed

        Returns the last row from the result set.

    .. php:method:: getFieldCount()

        :returns:    Number of fields in the result set
        :rtype:    int

        Returns the number of fields in the result set.

        Usage: see `Result Helper Methods`_.

    .. php:method:: getFieldNames()

        :returns:    Array of column names
        :rtype:    array

        Returns an array containing the field names in the
        result set.

    .. php:method:: getFieldData()

        :returns:    Array containing field meta-data
        :rtype:    array

        Generates an array of ``stdClass`` objects containing
        field meta-data.

    .. php:method:: getNumRows()

        :returns:    Number of rows in result set
        :rtype:    int

        Returns number of rows returned by the query

    .. php:method:: freeResult()

        :rtype:    void

        Frees a result set.

        Usage: see `Result Helper Methods`_.
