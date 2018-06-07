#######
Testing
#######

CodeIgniter has been built to make testing both the framework and your application as simple as possible.
Support for ``PHPUnit`` is built in, and the framework provides a number of convenient 
helper methods to make testing every aspect of your application as painless as possible.

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

PHPUnit Configuration
=====================

The framework has a ``phpunit.xml.dist`` file in the project root. This controls unit
testing of the framework itself. If you provide your own ``phpunit.xml``, it will
over-ride this.

Your ``phpunit.xml`` should exclude the ``system`` folder, as well as any ``vendor`` or
``ThirdParty`` folders, if you are unit testing your application.



Stream Filters
==============

Some stream filters have been provided as an alternate to these helper methods.

CITestStreamFilter
------------------

This filter captures output and makes it available to you.

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


Additional Assertions
=====================

``CIUnitTestCase`` provides additional unit testing assertions that you might find useful.

**assertLogged($level, $expectedMessage)**

Ensure that something you expected to be logged actually was::

        $config = new LoggerConfig();
        $logger = new Logger($config);

        ... do something that you expect a log entry from
        $logger->log('error', "That's no moon");

        $this->assertLogged('error', "That's no moon");

**assertEventTriggered($eventName)**

Ensure that an event you excpected to be triggered actually was::

    Events::on('foo', function($arg) use(&$result) {
        $result = $arg;
    });

    Events::trigger('foo', 'bar');

    $this->assertEventTriggered('foo');

