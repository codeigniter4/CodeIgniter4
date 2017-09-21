#######
Testing
#######

CodeIgniter has been built to make testing both the framework and your application as simple as possible.
Support for ``PHPUnit`` is built in, and a ``phpunit.xml`` file is already setup for your application.
It also provides a number of convenient helper methods to make testing every aspect of your application
as painless as possible.

.. contents::
    :local:
    :depth: 2

========================
Testing Your Application
========================

The Test Class
==============

In order to take advantage of the additional tools provided, your tests must extend ``\CIUnitTestCase``::

    class MyTests extends \CIUnitTestCase
    {
        . . .
    }

.. note:: More features are planned, but are not implemented yet. Stay tuned.

Mocking Services
================

You will often find that you need to mock one of the services defined in **application/Config/Services.php** to limit
your tests to only the code in question, while simulating various responses from the services. This is especially
true when testing controllers and other integration testing. CodeIgniter makes this simple.

While in test mode, the system loads a wrapper around the **Services** class that provides two new methods,
``injectMock()``, and ``reset()``.

**injectMock()**

This method allows you to define the exact instance that will be returned by the Services class. You can use this to
set properties of a service so that it behaves in a certain way, or replace a service with a mocked class.
::

    public function testSomething()
    {
        $curlrequest = $this->getMockBuilder('CodeIgniter\HTTP\CURLRequest')
                            ->setMethods(['request'])
                            ->getMock();
        Services::injectMock('curlrequest', $curlrequest);

        // Do normal testing here....
    }

The first parameter is the service that you are replacing. The name must match the function name in the Services
class exactly. The second parameter is the instance to replace it with.

**reset()**

Removes all mocked classes from the Services class, bringing it back to its original state.

===================
Testing Controllers
===================

Testing your controllers is made convenient with a couple of new helper classes and traits. When testing controllers,
you can execute the code within a controller, without first running through the entire application bootstrap process.

.. note:: Because the entire framework has not been bootstrapped, there will be times when you cannot test a controller
    this way.

The Helper Trait
================

You can use either of the base test classes described herein, but you do need to use the `ControllerTester` trait
within your tests::

    use Tests\Support\Helpers\ControllerTester;

    class TestControllerA extends CIDatabaseTestCase
    {
        use ControllerTester;
    }

Once the trait has been included, you can start setting up the environment, including the request and response classes,
the request body, URI, and more. You specify the controller to use with the ``controller()`` method, passing in the
fully qualified class name of your controller. Finally, call the ``execute()`` method with the name of the method
to run as the parameter::

    use Tests\Support\Helpers\ControllerTester;

    class TestControllerA extends CIDatabaseTestCase
    {
        use ControllerTester;

        public function testShowCategories()
        {
            $result = $this->withURI('http://example.com/categories')
			    ->controller(\App\Controllers\ForumController::class)
                ->execute('showCategories');

            $this->assertTrue($result->isOK());
        }
    }

Helper Methods
==============

**controller($class)**

Specifies the class name of the controller to test. The first parameter must be a fully qualified class name
(i.e. include the namespace)::

    $this->controller(\App\Controllers\ForumController::class);

**execute($method)**

Executes the specified method within the controller. The only parameter is the name of the method to run::

    $results = $this->controller(\App\Controllers\ForumController::class)
                     ->execute('showCategories');

This returns a new helper class that provides a number of routines for checking the response itself. See below
for details.

**withConfig($config)**

Allows you to pass in a modified version of **Config\App.php** to test with different settings::

    $config = new Config\App();
    $config->appTimezone = 'America/Chicago';

    $results = $this->withConfig($config)
                     ->controller(\App\Controllers\ForumController::class)
                     ->execute('showCategories');

If you do not provide one, the application's App config file will be used.

**withRequest($request)**

Allows you to provide an **IncomingRequest** instance tailored to your testing needs::

    $request = new CodeIgniter\HTTP\IncomingRequest(new Config\App(), new URI('http://example.com'));
    $request->setLocale($locale);

    $results = $this->withRequest($request)
                     ->controller(\App\Controllers\ForumController::class)
                     ->execute('showCategories');

If you do not provide one, a new IncomingRequest instance with the default application values will be passed
into your controller.

**withResponse($response)**

Allows you to provide a **Response** instance::

    $response = new CodeIgniter\HTTP\Response(new Config\App());

    $results = $this->withResponse($response)
                     ->controller(\App\Controllers\ForumController::class)
                     ->execute('showCategories');

If you do not provide one, a new Response instance with the default application values will be passed
into your controller.

**withURI($uri)**

Allows you to provide a new URI that simulates the URL the client was visiting when this controller was ran.
This is helpful if you need to check URI segments within your controller. The only parameter is a string
representing a valid URI::

    $results = $this->withURI('http://example.com/forums/categories')
                     ->controller(\App\Controllers\ForumController::class)
                     ->execute('showCategories');

It is a good practice to always provide the URI during testing to avoid surprises.

**withBody($body)**

Allows you to provide a custom body for the request. This can be helpful when testing API controllers where
you need to set a JSON value as the body. The only parameter is a string that represents the body of the request::

    $body = json_encode(['foo' => 'bar']);

    $results = $this->withBody($body)
                     ->controller(\App\Controllers\ForumController::class)
                     ->execute('showCategories');

Checking the Response
=====================

When the controller is executed, a new **ControllerResponse** instance will be returned that provides a number
of helpful methods, as well as direct access to the Request and Response that were generated.



=====================
Testing Your Database
=====================

The Test Class
==============

In order to take advantage of the built-in database tools that CodeIgniter provides for testing, your
tests must extend ``\CIDatabaseTestCase``::

    class MyTests extends \CIDatabaseTestCase
    {
        . . .
    }

Because special functionality is ran during the ``setUp()`` and ``tearDown()`` phases, you must ensure
that you call the parent's methods if you need to use those methods, otherwise you will lose much
of the functionality described here.
::

    class MyTests extends \CIDatabaseTestCase
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
step is to ensure that you have a ``tests`` database group setup in **application/Config/Database.php**.
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

    class MyTests extends \CIDatabaseTestCase
    {
        protected $refresh = true;
        protected $seed    = 'TestSeeder';
        protected $basePath = 'path/to/database/files';
    }

**$refresh**

This boolean value determines whether the database is completely refreshed before every test. If true,
all migrations are rolled back to version 0, then the database is migrated to the latest available migration.

**$seed**

If present and not empty, this specifies the name of a Seed file that is ran to populate the database with
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
must pe present within the path specified in ``$basePath``.

**dontSeeInDatabase($table, $criteria)**

Asserts that a row with criteria matching the key/value pairs in ``$criteria`` DOES NOT exist in the database.
::

    $criteria = [
        'email' => 'joe@example.com',
        'active' => 1
    ];
    $this->dontSeeInDatabase('users', $criteria);

**seeInDatabase($table, $criteria)**

Asserts that a row with criteria matching the key/value pairs in ``$criteria`` DOES exist in the database.
::

    $criteria = [
        'email' => 'joe@example.com',
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

