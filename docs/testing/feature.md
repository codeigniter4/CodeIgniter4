# HTTP Feature Testing

Feature testing allows you to view the results of a single call to your application. This might be returning the results of a single web form, hitting an API endpoint, and more. This is handy because it allows you to test the entire life-cycle of a single request, ensuring that the routing works and the response is in the correct format, analyzing the results, and more.

## The Test Class

Feature testing requires the `CodeIgniter\Test\FeatureTestTrait` trait. Add `CodeIgniter\Test\DatabaseTestTrait` when your tests also need database staging. Ensure that `parent::setUp()` and `parent::tearDown()` are called if you implement your own methods.

```php
<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class FooTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->myClassMethod();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->anotherClassMethod();
    }
}
```

## Requesting a Page

Essentially, feature tests simply allow you to call an endpoint on your application and get the results back. To do this, you use the `call()` method.

1.  The first parameter is the HTTP method to use (most frequently either `GET` or `POST`).

2.  The second parameter is the URI path on your site to test.

3.  The third parameter `$params` accepts an array that is used to populate the superglobal variables for the HTTP verb you are using. So, a method of **GET** would have the `$_GET` variable populated, while a **POST** request would have the `$_POST` array populated. The `$params` is also used in [Formatting the Request](#formatting-the-request).

    !!! note "Note"
        The `$params` array does not make sense for every HTTP verb, but is included for consistency.

```php
// Get a simple page
$result = $this->call('GET', '/');

// Submit a form
$result = $this->call('post', 'contact', [
    'name'  => 'Fred Flintstone',
    'email' => 'flintyfred@example.com',
]);
```

### Shorthand Methods

Shorthand methods for each of the HTTP verbs exist to ease typing and make things clearer:

```php
$this->get($path, $params);
$this->post($path, $params);
$this->put($path, $params);
$this->patch($path, $params);
$this->delete($path, $params);
$this->options($path, $params);
```

### Setting Different Routes

You can use a custom collection of routes by passing an array of "routes" into the `withRoutes()` method. This will override any existing routes in the system:

```php
$routes = [
    ['GET', 'users', 'UserController::list'],
];

$result = $this->withRoutes($routes)->get('users');
```

Each of the "routes" is a 3-element array containing the HTTP verb (or "add" for all), the URI to match, and the routing destination.

### Setting Session Values

You can set custom session values to use during a single test with the `withSession()` method. This takes an array of key/value pairs that should exist within the `$_SESSION` variable when this request is made, or `null` to indicate that the current values of `$_SESSION` should be used. This is handy for testing authentication and more.

```php
$values = [
    'logged_in' => 123,
];

$result = $this->withSession($values)->get('admin');

// Or...

$_SESSION['logged_in'] = 123;

$result = $this->withSession()->get('admin');
```

### Setting Headers

You can set header values with the `withHeaders()` method. This takes an array of key/value pairs that would be passed as a header into the call:

```php
$headers = [
    'CONTENT_TYPE' => 'application/json',
];

$result = $this->withHeaders($headers)->post('users');
```

### Bypassing Events

Events are handy to use in your application, but can be problematic during testing, especially events that are used to send out emails. You can tell the system to skip any event handling with the `skipEvents()` method:

```php
$result = $this->skipEvents()->post('users', $userInfo);
```

### Formatting the Request

You can set the format of your request's body using the `withBodyFormat()` method. Currently, this supports either `json` or `xml`. This is useful when testing JSON or XML APIs so that you can set the request in the form that the controller will expect.

This will take the parameters passed into `call()`, `post()`, `get()`... and assign them to the body of the request in the given format.

This will also set the `Content-Type` header for your request accordingly.

```php
// If your feature test contains this:
$result = $this->withBodyFormat('json')->post('users', $userInfo);

// Your controller can then get the parameters passed in with:
$userInfo = $this->request->getJson();
```

### Setting the Body

You can set the body of your request with the `withBody()` method. This allows you to format the body however you want. It is recommended that you use this if you have more complicated XML to test.

This will not set the `Content-Type` header for you. If you need that, you can set it with the `withHeaders()` method.

## Checking the Response

`FeatureTestTrait::call()` returns an instance of a `TestResponse`. See [Testing Responses](response.md) on how to use this class to perform additional assertions and verification in your test cases.
