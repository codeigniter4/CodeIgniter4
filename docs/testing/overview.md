# Testing

CodeIgniter has been built to make testing both the framework and your application as simple as possible. Support for [PHPUnit](https://phpunit.de/) is built in, and the framework provides a number of convenient helper methods to make testing every aspect of your application as painless as possible.

## System Setup

### Installing PHPUnit

CodeIgniter uses [PHPUnit](https://phpunit.de/) as the basis for all of its testing. There are two ways to install PHPUnit to use within your system.

#### Composer

The recommended method is to install it in your project using [Composer](https://getcomposer.org/). While it's possible to install it globally, we do not recommend it, since it can cause compatibility issues with other projects on your system as time goes on.

Ensure that you have Composer installed on your system. From the project root (the directory that contains the application and system directories) type the following from the command line:

```console
composer require --dev phpunit/phpunit
```

This will install the correct version for your current PHP version. Once that is done, you can run all of the tests for this project by typing:

```console
vendor/bin/phpunit
```

If you are using Windows, use the following command:

```console
vendor\bin\phpunit
```

#### Phar

The other option is to download the .phar file as described in the [PHPUnit installation guide](https://docs.phpunit.de/en/10.5/installation.html). This is a standalone file that should be placed within your project root.

## Testing Your Application

### PHPUnit Configuration

In your CodeIgniter project root, there is the `phpunit.dist.xml` file. This controls unit testing of your application. If you provide your own `phpunit.xml`, it will override this.

By default, test files are placed under the **tests** directory in the project root.

### The Test Class

In order to take advantage of the additional tools provided, your tests must extend `CodeIgniter\Test\CIUnitTestCase`.

There are no rules for how test files must be placed. However, we recommend that you establish placement rules in advance so that you can quickly understand where the test files are located.

In this document, the test files corresponding to the classes in the **app** directory will be placed in the **tests/app** directory. To test a new library, **app/Libraries/Foo.php**, you would create a new file at **tests/app/Libraries/FooTest.php**:

```php
<?php

namespace App\Libraries;

use CodeIgniter\Test\CIUnitTestCase;

class FooTest extends CIUnitTestCase
{
    public function testFooNotBar()
    {
        // ...
    }
}
```

To test one of your models, **app/Models/UserModel.php**, you might end up with something like this in **tests/app/Models/UserModelTest.php**:

```php
<?php

namespace App\Models;

use CodeIgniter\Test\CIUnitTestCase;

class UserModelTest extends CIUnitTestCase
{
    public function testFooNotBar()
    {
        // ...
    }
}
```

You can create any directory structure that fits your testing style/needs. When namespacing the test classes, remember that the **app** directory is the root of the `App` namespace, so any classes you use must have the correct namespace relative to `App`.

!!! note "Note"
    Namespaces are not strictly required for test classes, but they are helpful to ensure no class names collide.

When testing database results, you must use the [DatabaseTestTrait](database.md) in your class.

#### Staging

Most tests require some preparation in order to run correctly. PHPUnit's `TestCase` provides four methods to help with staging and cleanup:

```
public static function setUpBeforeClass(): void
public static function tearDownAfterClass(): void

protected function setUp(): void
protected function tearDown(): void
```

The static methods `setUpBeforeClass()` and `tearDownAfterClass()` run before and after the entire test case, whereas the protected methods `setUp()` and `tearDown()` run between each test.

If you implement any of these special functions, make sure you run their parent as well so extended test cases do not interfere with staging:

```php
<?php

namespace App\Models;

use CodeIgniter\Test\CIUnitTestCase;

final class UserModelTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp(); // Do not forget

        helper('text');
    }

    // ...
}
```

#### Traits

A common way to enhance your tests is to use traits to consolidate staging across different test cases. `CIUnitTestCase` will detect any class traits and look for staging methods to run named for the trait itself (i.e., `setUp{NameOfTrait}()` and `tearDown{NameOfTrait}()`).

For example, if you needed to add authentication to some of your test cases, you could create an authentication trait with a setup method to fake a logged-in user:

```php
<?php

namespace App\Traits;

trait AuthTrait
{
    protected function setUpAuthTrait()
    {
        $user = $this->createFakeUser();
        $this->logInUser($user);
    }

    // ...
}
```

```php
<?php

namespace Tests;

use App\Traits\AuthTrait;
use CodeIgniter\Test\CIUnitTestCase;

final class AuthenticationFeatureTest extends CIUnitTestCase
{
    use AuthTrait;

    // ...
}
```

#### Additional Assertions

`CIUnitTestCase` provides additional unit testing assertions that you might find useful.

##### assertLogged($level, $expectedMessage)

Ensure that something you expected to be logged was actually logged:

```php
$config = new \Config\Logger();
$logger = new \CodeIgniter\Log\Logger($config);

// check verbatim the log message
$logger->log('error', "That's no moon");
$this->assertLogged('error', "That's no moon");
```

##### assertLogContains($level, $logMessage)

Ensure that there's a record in the logs which contains a message part.

```php
// check that a portion of the message is found in the logs
$exception = new \RuntimeException('Hello world.');
$logger->log('error', $exception->getTraceAsString());
$this->assertLogContains('error', '{main}');
```

##### assertEventTriggered($eventName)

Ensure that an event you expected to be triggered actually was:

```php
<?php

use CodeIgniter\Events\Events;

Events::on('foo', static function ($arg) use (&$result) {
    $result = $arg;
});

Events::trigger('foo', 'bar');

$this->assertEventTriggered('foo');
```

##### assertHeaderEmitted($header, $ignoreCase = false)

Ensure that a header or cookie was actually emitted:

```php
<?php

$response->setCookie('foo', 'bar');

ob_start();
$this->response->send();
$output = ob_get_clean(); // in case you want to check the actual body

$this->assertHeaderEmitted('Set-Cookie: foo=bar');
```

!!! note "Note"
    The test case with this should be run as a separate process (with [@runInSeparateProcess annotation](https://docs.phpunit.de/en/10.5/annotations.html#runinseparateprocess) or [RunInSeparateProcess attribute](https://docs.phpunit.de/en/10.5/attributes.html#runinseparateprocess)) in PHPUnit.

##### assertHeaderNotEmitted($header, $ignoreCase = false)

Ensure that a header or cookie was not emitted:

```php
<?php

$response->setCookie('foo', 'bar');

ob_start();
$this->response->send();
$output = ob_get_clean(); // in case you want to check the actual body

$this->assertHeaderNotEmitted('Set-Cookie: banana');
```

!!! note "Note"
    The test case with this should be run as a separate process (with [@runInSeparateProcess annotation](https://docs.phpunit.de/en/10.5/annotations.html#runinseparateprocess) or [RunInSeparateProcess attribute](https://docs.phpunit.de/en/10.5/attributes.html#runinseparateprocess)) in PHPUnit.

##### assertCloseEnough($expected, $actual, $message = '', $tolerance = 1)

For extended execution time testing, tests that the absolute difference between expected and actual time is within the prescribed tolerance:

```php
<?php

use CodeIgniter\Debug\Timer;

$timer = new Timer();
$timer->start('longjohn', strtotime('-11 minutes'));
$this->assertCloseEnough(11 * 60, $timer->getElapsedTime('longjohn'));
```

The above test will allow the actual time to be either 660 or 661 seconds.

##### assertCloseEnoughString($expected, $actual, $message = '', $tolerance = 1)

For extended execution time testing, tests that the absolute difference between expected and actual time, formatted as strings, is within the prescribed tolerance:

```php
<?php

use CodeIgniter\Debug\Timer;

$timer = new Timer();
$timer->start('longjohn', strtotime('-11 minutes'));
$this->assertCloseEnoughString(11 * 60, $timer->getElapsedTime('longjohn'));
```

The above test will allow the actual time to be either 660 or 661 seconds.

#### Accessing Protected/Private Properties

When testing, you can use the following setter and getter methods to access protected and private methods and properties in the classes that you are testing.

##### getPrivateMethodInvoker($instance, $method)

Enables you to call private methods from outside the class. This returns a function that can be called. The first parameter is an instance of the class to test. The second parameter is the name of the method you want to call.

```php
<?php

use App\Libraries\Foo;

// Create an instance of the class to test
$obj = new Foo();

// Get the invoker for the 'privateMethod' method.
$method = self::getPrivateMethodInvoker($obj, 'privateMethod');

// Test the results
$this->assertEquals('bar', $method('param1', 'param2'));
```

##### getPrivateProperty($instance, $property)

Retrieves the value of a private/protected class property from an instance of a class. The first parameter is an instance of the class to test. The second parameter is the name of the property.

```php
<?php

use App\Libraries\Foo;

// Create an instance of the class to test
$obj = new Foo();

// Test the value
$this->assertEquals('bar', $this->getPrivateProperty($obj, 'baz'));
```

##### setPrivateProperty($instance, $property, $value)

Set a protected value within a class instance. The first parameter is an instance of the class to test. The second parameter is the name of the property to set the value of. The third parameter is the value to set it to:

```php
<?php

use App\Libraries\Foo;

// Create an instance of the class to test
$obj = new Foo();

// Set the value
$this->setPrivateProperty($obj, 'baz', 'oops!');

// Do normal testing...
```

### Mocking Services

You will often find that you need to mock one of the services defined in **app/Config/Services.php** to limit your tests to only the code in question, while simulating various responses from the services. This is especially true when testing controllers and other integration testing. The **Services** class provides the following methods to simplify this.

#### Services::injectMock()

This method allows you to define the exact instance that will be returned by the Services class. You can use this to set properties of a service so that it behaves in a certain way, or replace a service with a mocked class.

```php
<?php

namespace Tests;

use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

final class SomeTest extends CIUnitTestCase
{
    public function testSomething()
    {
        $curlrequest = $this->getMockBuilder(CURLRequest::class)
            ->onlyMethods(['request'])
            ->getMock();
        Services::injectMock('curlrequest', $curlrequest);

        // Do normal testing here....
    }
}
```

The first parameter is the service that you are replacing. The name must match the function name in the Services class exactly. The second parameter is the instance to replace it with.

#### Services::reset()

Removes all mocked classes from the Services class, bringing it back to its original state.

You can also use the `$this->resetServices()` method that `CIUnitTestCase` provides.

!!! note "Note"
    This method resets all states of Services, and the `RouteCollection` will have no routes. If you want your routes to be loaded, you need to call the `loadRoutes()` method like `Services::routes()->loadRoutes()`.

#### Services::resetSingle(string $name)

Removes any mock and shared instances for a single service, by its name.

!!! note "Note"
    The `Cache`, `Email` and `Session` services are mocked by default to prevent intrusive testing behavior. To prevent these from being mocked, remove their method callback from the class property: `$setUpMethods = ['mockEmail', 'mockSession'];`

### Mocking Factory Instances

Similar to Services, you may find yourself needing to supply a pre-configured class instance during testing that will be used with `Factories`. Use the same `Factories::injectMock()` and `Factories::reset()` static methods as **Services**, but they take an additional preceding parameter for the component name:

```php
<?php

namespace Tests;

use App\Models\UserModel;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Mock\MockUserModel;

final class SomeTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $model = new MockUserModel();
        Factories::injectMock('models', UserModel::class, $model);
    }
}
```

!!! note "Note"
    All component Factories are reset by default between each test. Modify your test case's `$setUpMethods` if you need instances to persist.

### Testing and Time

Testing time-dependent code can be challenging. However, when using the [Time](../libraries/time.md) class, the current time can be fixed or changed at will during testing.

Below is sample test code that fixes the current time:

```php
<?php

namespace Tests;

use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;

final class TimeDependentCodeTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        // Reset the current time.
        Time::setTestNow();
    }

    public function testFixTime(): void
    {
        // Fix the current time to "2023-11-25 12:00:00".
        Time::setTestNow('2023-11-25 12:00:00');

        // This assertion always passes.
        $this->assertSame('2023-11-25 12:00:00', (string) Time::now());
    }
}
```

You can fix the current time with the `Time::setTestNow()` method. Optionally, you can specify a locale as the second parameter.

Don't forget to reset the current time after the test by calling it without parameters.
