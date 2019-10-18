#######
Testing
#######

CodeIgniter has been built to make testing both the framework and your application as simple as possible.
Support for ``PHPUnit`` is built in, and the framework provides a number of convenient
helper methods to make testing every aspect of your application as painless as possible.

.. contents::
    :local:
    :depth: 2

************
System Set Up
************

Installing phpUnit
==================

CodeIgniter uses `phpUnit <https://phpunit.de/>`__ as the basis for all of its testing. There are two ways to install
phpUnit to use within your system.

Composer
--------

The recommended method is to install it in your project using `Composer <https://getcomposer.org/>`__. While it's possible
to install it globally we do not recommend it, since it can cause compatibility issues with other projects on your
system as time goes on.

Ensure that you have Composer installed on your system. From the project root (the directory that contains the
application and system directories) type the following from the command line::

    > composer require --dev phpunit/phpunit

This will install the correct version for your current PHP version. Once that is done, you can run all of the
tests for this project by typing::

    > ./vendor/bin/phpunit

Phar
----

The other option is to download the .phar file from the `phpUnit <https://phpunit.de/getting-started/phpunit-7.html>`__ site.
This is a standalone file that should be placed within your project root.


************************
Testing Your Application
************************

PHPUnit Configuration
=====================

The framework has a ``phpunit.xml.dist`` file in the project root. This controls unit
testing of the framework itself. If you provide your own ``phpunit.xml``, it will
over-ride this.

Your ``phpunit.xml`` should exclude the ``system`` folder, as well as any ``vendor`` or
``ThirdParty`` folders, if you are unit testing your application.

The Test Class
==============

In order to take advantage of the additional tools provided, your tests must extend ``\CIUnitTestCase``. All tests
are expected to be located in the **tests/app** directory by default.

To test a new library, **Foo**, you would create a new file at **tests/app/Libraries/FooTest.php**::

    <?php namespace App\Libraries;

    class FooTest extends \CIUnitTestCase
    {
        public function testFooNotBar()
        {
            . . .
        }
    }

To test one of your models, you might end up with something like this in ``tests/app/Models/OneOfMyModelsTest.php``::

    <?php namespace App\Models;

    class OneOfMyModelsTest extends \CIUnitTestCase
    {
        public function testFooNotBar()
        {
            . . .
        }
    }


You can create any directory structure that fits your testing style/needs. When namespacing the test classes,
remember that the **app** directory is the root of the ``App`` namespace, so any classes you use must
have the correct namespace relative to ``App``.

.. note:: Namespaces are not strictly required for test classes, but they are helpful to ensure no class names collide.

When testing database results, you must use the `CIDatabaseTestClass <database.html>`_ class.

Additional Assertions
---------------------

``CIUnitTestCase`` provides additional unit testing assertions that you might find useful.

**assertLogged($level, $expectedMessage)**

Ensure that something you expected to be logged actually was::

        $config = new LoggerConfig();
        $logger = new Logger($config);

        ... do something that you expect a log entry from
        $logger->log('error', "That's no moon");

        $this->assertLogged('error', "That's no moon");

**assertEventTriggered($eventName)**

Ensure that an event you expected to be triggered actually was::

    Events::on('foo', function($arg) use(&$result) {
        $result = $arg;
    });

    Events::trigger('foo', 'bar');

    $this->assertEventTriggered('foo');

**assertHeaderEmitted($header, $ignoreCase=false)**

Ensure that a header or cookie was actually emitted::

    $response->setCookie('foo', 'bar');

    ob_start();
    $this->response->send();
    $output = ob_get_clean(); // in case you want to check the adtual body

    $this->assertHeaderEmitted("Set-Cookie: foo=bar");

Note: the test case with this should be `run as a separate process
in PHPunit <https://phpunit.readthedocs.io/en/7.4/annotations.html#runinseparateprocess>`_.

**assertHeaderNotEmitted($header, $ignoreCase=false)**

Ensure that a header or cookie was not emitted::

    $response->setCookie('foo', 'bar');

    ob_start();
    $this->response->send();
    $output = ob_get_clean(); // in case you want to check the adtual body

    $this->assertHeaderNotEmitted("Set-Cookie: banana");

Note: the test case with this should be `run as a separate process
in PHPunit <https://phpunit.readthedocs.io/en/7.4/annotations.html#runinseparateprocess>`_.

**assertCloseEnough($expected, $actual, $message='', $tolerance=1)**

For extended execution time testing, tests that the absolute difference
between expected and actual time is within the prescribed tolerance.::

    $timer = new Timer();
    $timer->start('longjohn', strtotime('-11 minutes'));
    $this->assertCloseEnough(11 * 60, $timer->getElapsedTime('longjohn'));

The above test will allow the actual time to be either 660 or 661 seconds.

**assertCloseEnoughString($expected, $actual, $message='', $tolerance=1)**

For extended execution time testing, tests that the absolute difference
between expected and actual time, formatted as strings, is within the prescribed tolerance.::

    $timer = new Timer();
    $timer->start('longjohn', strtotime('-11 minutes'));
    $this->assertCloseEnoughString(11 * 60, $timer->getElapsedTime('longjohn'));

The above test will allow the actual time to be either 660 or 661 seconds.


Accessing Protected/Private Properties
--------------------------------------

When testing, you can use the following setter and getter methods to access protected and private methods and
properties in the classes that you are testing.

**getPrivateMethodInvoker($instance, $method)**

Enables you to call private methods from outside the class. This returns a function that can be called. The first
parameter is an instance of the class to test. The second parameter is the name of the method you want to call.

::

    // Create an instance of the class to test
    $obj = new Foo();

    // Get the invoker for the 'privateMethod' method.
	$method = $this->getPrivateMethodInvoker($obj, 'privateMethod');

    // Test the results
	$this->assertEquals('bar', $method('param1', 'param2'));

**getPrivateProperty($instance, $property)**

Retrieves the value of a private/protected class property from an instance of a class. The first parameter is an
instance of the class to test. The second parameter is the name of the property.

::

    // Create an instance of the class to test
    $obj = new Foo();

    // Test the value
    $this->assertEquals('bar', $this->getPrivateProperty($obj, 'baz'));

**setPrivateProperty($instance, $property, $value)**

Set a protected value within a class instance. The first parameter is an instance of the class to test. The second
parameter is the name of the property to set the value of. The third parameter is the value to set it to::

    // Create an instance of the class to test
    $obj = new Foo();

    // Set the value
    $this->setPrivateProperty($obj, 'baz', 'oops!');

    // Do normal testing...

Mocking Services
================

You will often find that you need to mock one of the services defined in **app/Config/Services.php** to limit
your tests to only the code in question, while simulating various responses from the services. This is especially
true when testing controllers and other integration testing. The **Services** class provides two methods to make this
simple: ``injectMock()``, and ``reset()``.

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



Stream Filters
==============

**CITestStreamFilter** provides an alternate to these helper methods.

You may need to test things that are difficult to test. Sometimes, capturing a stream, like PHP's own STDOUT, or STDERR,
might be helpful. The ``CITestStreamFilter`` helps you capture the output from the stream of your choice.

An example demonstrating this inside one of your test cases::

    public function setUp()
    {
        CITestStreamFilter::$buffer = '';
        $this->stream_filter = stream_filter_append(STDOUT, 'CITestStreamFilter');
    }

    public function tearDown()
    {
        stream_filter_remove($this->stream_filter);
    }

    public function testSomeOutput()
    {
        CLI::write('first.');
        $expected = "first.\n";
        $this->assertEquals($expected, CITestStreamFilter::$buffer);
    }
