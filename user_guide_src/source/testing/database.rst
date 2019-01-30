=====================
Testing Your Database
=====================

.. contents::
    :local:
    :depth: 2

The Test Class
==============

In order to take advantage of the built-in database tools that CodeIgniter provides for testing, your
tests must extend ``CIDatabaseTestCase``::

    <?php namespace App\Database;

    use CodeIgniter\Test\CIDatabaseTestCase;

    class MyTests extends CIDatabaseTestCase
    {
        . . .
    }

Because special functionality executed during the ``setUp()`` and ``tearDown()`` phases, you must ensure
that you call the parent's methods if you need to use those methods, otherwise you will lose much
of the functionality described here::

    <?php namespace App\Database;

    use CodeIgniter\Test\CIDatabaseTestCase;

    class MyTests extends CIDatabaseTestCase
    {
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

Test Database Setup
===================

When running database tests, you need to provide a database that can be used during testing. Instead of
using the PHPUnit built-in database features, the framework provides tools specific to CodeIgniter. The first
step is to ensure that you have a ``tests`` database group setup in **app/Config/Database.php**.
This specifies a database connection that is only used while running tests, to keep your other data safe.

If you have multiple developers on your team, you will likely want to keep your credentials store in
the **.env** file. To do so, edit the file to ensure the following lines are present, and have the
correct information::

    database.tests.dbdriver = 'MySQLi';
    database.tests.username = 'root';
    database.tests.password = '';
    database.tests.database = '';

Migrations and Seeds
--------------------

When running tests you need to ensure that your database has the correct schema setup, and that
it is in a known state for every test. You can use migrations and seeds to setup your database,
by adding a couple of class properties to your test.
::

    <?php namespace App\Database;

    use CodeIgniter\Test\CIDatabaseTestCase;

    class MyTests extends\CIDatabaseTestCase
    {
        protected $refresh  = true;
        protected $seed     = 'TestSeeder';
        protected $basePath = 'path/to/database/files';
    }

**$refresh**

This boolean value determines whether the database is completely refreshed before every test. If true,
all migrations are rolled back to version 0, then the database is migrated to the latest available migration.

**$seed**

If present and not empty, this specifies the name of a Seed file that is used to populate the database with
test data prior to every test running.

**$basePath**

By default, CodeIgniter will look in **tests/_support/database/migrations** and **tests/_support_database/seeds**
to locate the migrations and seeds that it should run during testing. You can change this directory by specifying
the path in the ``$basePath`` property. This should not include the **migrations** or **seeds** directories, but
the path to the single directory that holds both of those sub-directories.

Helper Methods
==============

The **CIDatabaseTestCase** class provides several helper methods to aid in testing your database.

**seed($name)**

Allows you to manually load a Seed into the database. The only parameter is the name of the seed to run. The seed
must be present within the path specified in ``$basePath``.

**dontSeeInDatabase($table, $criteria)**

Asserts that a row with criteria matching the key/value pairs in ``$criteria`` DOES NOT exist in the database.
::

    $criteria = [
        'email'  => 'joe@example.com',
        'active' => 1
    ];
    $this->dontSeeInDatabase('users', $criteria);

**seeInDatabase($table, $criteria)**

Asserts that a row with criteria matching the key/value pairs in ``$criteria`` DOES exist in the database.
::

    $criteria = [
        'email'  => 'joe@example.com',
        'active' => 1
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
        'name'  => 'Joe Cool'
    ];
    $this->hasInDatabase('users', $data);

**seeNumRecords($expected, $table, $criteria)**

Asserts that a number of matching rows are found in the database that match ``$criteria``.
::

    $criteria = [
        'deleted' => 1
    ];
    $this->seeNumRecords(2, 'users', $criteria);

