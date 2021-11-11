=====================
Testing Your Database
=====================

.. contents::
    :local:
    :depth: 2

The Test Class
==============

In order to take advantage of the built-in database tools that CodeIgniter provides for testing, your
tests must extend ``CIUnitTestCase`` and use the ``DatabaseTestTrait``::

    <?php

    namespace App\Database;

    use CodeIgniter\Test\CIUnitTestCase;
    use CodeIgniter\Test\DatabaseTestTrait;

    class MyTests extends CIUnitTestCase
    {
        use DatabaseTestTrait;

        // ...
    }

Because special functionality executed during the ``setUp()`` and ``tearDown()`` phases, you must ensure
that you call the parent's methods if you need to use those methods, otherwise you will lose much
of the functionality described here::

    <?php

    namespace App\Database;

    use CodeIgniter\Test\CIUnitTestCase;
    use CodeIgniter\Test\DatabaseTestTrait;

    class MyTests extends CIUnitTestCase
    {
        use DatabaseTestTrait;

        public function setUp()
        {
            parent::setUp();

            // Do something here....
        }

        public function tearDown()
        {
            parent::tearDown();

            // Do something here....
        }
    }

Setting Up a Test Database
==========================

When running database tests, you need to provide a database that can be used during testing. Instead of
using the PHPUnit built-in database features, the framework provides tools specific to CodeIgniter. The first
step is to ensure that you have set up a ``tests`` database group in **app/Config/Database.php**.
This specifies a database connection that is only used while running tests, to keep your other data safe.

If you have multiple developers on your team, you will likely want to keep your credentials stored in
the **.env** file. To do so, edit the file to ensure the following lines are present and have the
correct information::

    database.tests.dbdriver = 'MySQLi';
    database.tests.username = 'root';
    database.tests.password = '';
    database.tests.database = '';

Migrations and Seeds
--------------------

When running tests, you need to ensure that your database has the correct schema set up and that
it is in a known state for every test. You can use migrations and seeds to set up your database,
by adding a couple of class properties to your test.
::

    <?php

    namespace App\Database;

    use CodeIgniter\Test\CIUnitTestCase;
    use CodeIgniter\Test\DatabaseTestTrait;

    class MyTests extends CIUnitTestCase
    {
        use DatabaseTestTrait;

        protected $refresh  = true;
        protected $seed     = 'TestSeeder';
        protected $basePath = 'path/to/database/files';
    }

**$migrate**

This boolean value determines whether the database migration runs before test.
By default, the database is always migrated to the latest available state as defined by ``$namespace``.
If false, migration never runs. If you want to disable migration, set false.

**$migrateOnce**

This boolean value determines whether the database migration runs only once. If you want
to run migration once before the first test, set true. If not present or false, migration
runs before each test.

**$refresh**

This boolean value determines whether the database is completely refreshed before test. If true,
all migrations are rolled back to version 0.

**$seed**

If present and not empty, this specifies the name of a Seed file that is used to populate the database with
test data prior to test running.

**$seedOnce**

This boolean value determines whether the database seeding runs only once. If you want
to run database seeding once before the first test, set true. If not present or false, database seeding
runs before each test.

**$basePath**

By default, CodeIgniter will look in **tests/_support/Database/Seeds** to locate the seeds that it should run during testing.
You can change this directory by specifying the ``$basePath`` property. This should not include the **Seeds** directory,
but the path to the single directory that holds the sub-directory.

**$namespace**

By default, CodeIgniter will look in **tests/_support/Database/Migrations** to locate the migrations
that it should run during testing. You can change this location by specifying a new namespace in the ``$namespace`` properties.
This should not include the **Database\\Migrations** sub-namespace but just the base namespace.
To run migrations from all available namespaces set this property to ``null``.

Helper Methods
==============

The **DatabaseTestTrait** class provides several helper methods to aid in testing your database.

**regressDatabase()**

Called during ``$refresh`` described above, this method is available if you need to reset the database manually.

**migrateDatabase()**

Called during ``setUp``, this method is available if you need to run migrations manually.

**seed($name)**

Allows you to manually load a Seed into the database. The only parameter is the name of the seed to run. The seed
must be present within the path specified in ``$basePath``.

**dontSeeInDatabase($table, $criteria)**

Asserts that a row with criteria matching the key/value pairs in ``$criteria`` DOES NOT exist in the database.
::

    $criteria = [
        'email'  => 'joe@example.com',
        'active' => 1,
    ];
    $this->dontSeeInDatabase('users', $criteria);

**seeInDatabase($table, $criteria)**

Asserts that a row with criteria matching the key/value pairs in ``$criteria`` DOES exist in the database.
::

    $criteria = [
        'email'  => 'joe@example.com',
        'active' => 1,
    ];
    $this->seeInDatabase('users', $criteria);

**grabFromDatabase($table, $column, $criteria)**

Returns the value of ``$column`` from the specified table where the row matches ``$criteria``. If more than one
row is found, it will only test against the first one.
::

    $username = $this->grabFromDatabase('users', 'username', ['email' => 'joe@example.com']);

**hasInDatabase($table, $data)**

Inserts a new row into the database. This row is removed after the current test runs. ``$data`` is an associative
array with the data to insert into the table.
::

    $data = [
        'email' => 'joe@example.com',
        'name'  => 'Joe Cool',
    ];
    $this->hasInDatabase('users', $data);

**seeNumRecords($expected, $table, $criteria)**

Asserts that a number of matching rows are found in the database that match ``$criteria``.
::

    $criteria = [
        'active' => 1,
    ];
    $this->seeNumRecords(2, 'users', $criteria);
