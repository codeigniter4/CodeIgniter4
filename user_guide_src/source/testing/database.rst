#####################
Testing Your Database
#####################

.. contents::
    :local:
    :depth: 2

The Test Class
**************

In order to take advantage of the built-in database tools that CodeIgniter provides for testing, your
tests must extend ``CIUnitTestCase`` and use the ``DatabaseTestTrait``:

.. literalinclude:: database/001.php

Because special functionality executed during the ``setUp()`` and ``tearDown()`` phases, you must ensure
that you call the parent's methods if you need to use those methods, otherwise you will lose much
of the functionality described here:

.. literalinclude:: database/002.php

Setting Up a Test Database
**************************

When running database tests, you need to provide a database that can be used during testing. Instead of
using the PHPUnit built-in database features, the framework provides tools specific to CodeIgniter. The first
step is to ensure that you have set up a ``tests`` database group in **app/Config/Database.php**.
This specifies a database connection that is only used while running tests, to keep your other data safe.

If you have multiple developers on your team, you will likely want to keep your credentials stored in
the **.env** file. To do so, edit the file to ensure the following lines are present and have the
correct information::

    database.tests.hostname = localhost
    database.tests.database = ci4_test
    database.tests.username = root
    database.tests.password = root
    database.tests.DBDriver = MySQLi
    database.tests.DBPrefix =
    database.tests.port = 3306

Migrations and Seeds
====================

When running tests, you need to ensure that your database has the correct schema set up and that
it is in a known state for every test. You can use migrations and seeds to set up your database,
by adding a couple of class properties to your test.

.. literalinclude:: database/003.php

Migrations
----------

$migrate
^^^^^^^^

This boolean value determines whether the database migration runs before test.
By default, the database is always migrated to the latest available state as defined by ``$namespace``.
If ``false``, migration never runs. If you want to disable migration, set ``false``.

$migrateOnce
^^^^^^^^^^^^

This boolean value determines whether the database migration runs only once. If you want
to run migration once before the first test, set ``true``. If not present or ``false``, migration
runs before each test.

$refresh
^^^^^^^^

This boolean value determines whether the database is completely refreshed before test. If ``true``,
all migrations are rolled back to version 0.

$namespace
^^^^^^^^^^

By default, CodeIgniter will look in **tests/_support/Database/Migrations** to locate the migrations
that it should run during testing. You can change this location by specifying a new namespace in the ``$namespace`` properties.
This should not include the **Database\\Migrations** sub-namespace but just the base namespace.

.. important:: If you set this property to ``null``, it runs migrations from all available namespaces like ``php spark migrate --all``.

Seeds
-----

$seed
^^^^^

If present and not empty, this specifies the name of a Seed file that is used to populate the database with
test data prior to test running.

$seedOnce
^^^^^^^^^

This boolean value determines whether the database seeding runs only once. If you want
to run database seeding once before the first test, set ``true``. If not present or ``false``, database seeding
runs before each test.

$basePath
^^^^^^^^^

By default, CodeIgniter will look in **tests/_support/Database/Seeds** to locate the seeds that it should run during testing.
You can change this directory by specifying the ``$basePath`` property. This should not include the **Seeds** directory,
but the path to the single directory that holds the sub-directory.

Helper Methods
**************

The **DatabaseTestTrait** class provides several helper methods to aid in testing your database.

Changing Database State
=======================

regressDatabase()
-----------------

Called during ``$refresh`` described above, this method is available if you need to reset the database manually.

migrateDatabase()
-----------------

Called during ``setUp()``, this method is available if you need to run migrations manually.

seed($name)
-----------

Allows you to manually load a Seed into the database. The only parameter is the name of the seed to run. The seed
must be present within the path specified in ``$basePath``.

hasInDatabase($table, $data)
----------------------------

Inserts a new row into the database. This row is removed after the current test runs. ``$data`` is an associative
array with the data to insert into the table.

.. literalinclude:: database/007.php

Getting Data from Database
==========================

grabFromDatabase($table, $column, $criteria)
--------------------------------------------

Returns the value of ``$column`` from the specified table where the row matches ``$criteria``. If more than one
row is found, it will only return the first one.

.. literalinclude:: database/006.php

Assertions
==========

dontSeeInDatabase($table, $criteria)
------------------------------------

Asserts that a row with criteria matching the key/value pairs in ``$criteria`` DOES NOT exist in the database.

.. literalinclude:: database/004.php

seeInDatabase($table, $criteria)
--------------------------------

Asserts that a row with criteria matching the key/value pairs in ``$criteria`` DOES exist in the database.

.. literalinclude:: database/005.php

seeNumRecords($expected, $table, $criteria)
-------------------------------------------

Asserts that a number of matching rows are found in the database that match ``$criteria``.

.. literalinclude:: database/008.php
