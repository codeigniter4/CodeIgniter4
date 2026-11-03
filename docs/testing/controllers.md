# Testing Controllers

Testing your controllers is made convenient with a couple of new helper classes and traits. When testing controllers, you can execute the code within a controller, without first running through the entire application bootstrap process. Often, using the [Feature Testing tools](feature.md) will be simpler, but this functionality is here in case you need it.

!!! note "Note"
    Because the entire framework has not been bootstrapped, there will be times when you cannot test a controller this way.

## The Helper Trait

To enable Controller Testing, you need to use the `ControllerTestTrait` trait within your tests:

```php
<?php

namespace App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

class FooControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    use DatabaseTestTrait;
}
```

Once the trait has been included, you can start setting up the environment, including the request and response classes, the request body, URI, and more. You specify the controller to use with the `controller()` method, passing in the fully qualified class name of your controller. Finally, call the `execute()` method with the name of the method to run as the parameter:

```php
<?php

namespace App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

class ForumControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    use DatabaseTestTrait;

    public function testShowCategories()
    {
        $result = $this->withUri('http://example.com/categories')
            ->controller(ForumController::class)
            ->execute('showCategories');

        $this->assertTrue($result->isOK());
    }
}
```

## Helper Methods

### controller($class)

Specifies the class name of the controller to test. The first parameter must be a fully qualified class name (i.e., include the namespace):

```php
<?php

$this->controller(\App\Controllers\ForumController::class);
```

### execute(string $method, ...$params)

Executes the specified method within the controller. The first parameter is the name of the method to run:

```php
<?php

$results = $this->controller(\App\Controllers\ForumController::class)
    ->execute('showCategories');
```

By specifying the second and subsequent parameters, you can pass them to the controller method.

This returns a new helper class that provides a number of routines for checking the response itself. See below for details.

### withConfig($config)

Allows you to pass in a modified version of **app/Config/App.php** to test with different settings:

```php
<?php

$config              = new \Config\App();
$config->appTimezone = 'America/Chicago';

$results = $this->withConfig($config)
    ->controller(\App\Controllers\ForumController::class)
    ->execute('showCategories');
```

If you do not provide one, the application's App config file will be used.

### withRequest($request)

Allows you to provide an **IncomingRequest** instance tailored to your testing needs:

```php
<?php

$request = new \CodeIgniter\HTTP\IncomingRequest(
    new \Config\App(),
    new \CodeIgniter\HTTP\URI('http://example.com'),
    null,
    new \CodeIgniter\HTTP\UserAgent(),
);

$request->setLocale($locale);

$results = $this->withRequest($request)
    ->controller(\App\Controllers\ForumController::class)
    ->execute('showCategories');
```

If you do not provide one, a new IncomingRequest instance with the default application values will be passed into your controller.

### withResponse($response)

Allows you to provide a **Response** instance:

```php
<?php

$response = new \CodeIgniter\HTTP\Response(new \Config\App());

$results = $this->withResponse($response)
    ->controller(\App\Controllers\ForumController::class)
    ->execute('showCategories');
```

If you do not provide one, a new Response instance with the default application values will be passed into your controller.

### withLogger($logger)

Allows you to provide a **Logger** instance:

```php
<?php

$logger = new \CodeIgniter\Log\Handlers\FileHandler();

$results = $this->withResponse($response)
    ->withLogger($logger)
    ->controller(\App\Controllers\ForumController::class)
    ->execute('showCategories');
```

If you do not provide one, a new Logger instance with the default configuration values will be passed into your controller.

### withUri(string $uri)

Allows you to provide a new URI that simulates the URL the client was visiting when this controller was run. This is helpful if you need to check URI segments within your controller. The only parameter is a string representing a valid URI:

```php
<?php

$results = $this->withUri('http://example.com/forums/categories')
    ->controller(\App\Controllers\ForumController::class)
    ->execute('showCategories');
```

It is a good practice to always provide the URI during testing to avoid surprises.

!!! note "Note"
    Since v4.4.0, this method creates a new Request instance with the URI, because the Request instance should have the URI instance. Also, if the hostname in the URI string is invalid with `Config\App`, the valid hostname will be set.

### withBody($body)

Allows you to provide a custom body for the request. This can be helpful when testing API controllers where you need to set a JSON value as the body. The only parameter is a string that represents the body of the request:

```php
<?php

$body = json_encode(['foo' => 'bar']);

$results = $this->withBody($body)
    ->controller(\App\Controllers\ForumController::class)
    ->execute('showCategories');
```

## Checking the Response

`ControllerTestTrait::execute()` returns an instance of a `TestResponse`. See [Testing Responses](response.md) on how to use this class to perform additional assertions and verification in your test cases.

## Filter Testing

Similar to Controller Testing, the framework provides tools to help with creating tests for custom [Filters](../incoming/filters.md) and your project's use of them in routing.

### The Helper Trait

Just like with the Controller Tester, you need to include the `FilterTestTrait` in your test cases to enable these features:

```php
<?php

namespace App\Filters;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FilterTestTrait;

class FooFilterTest extends CIUnitTestCase
{
    use FilterTestTrait;
}
```

### Configuration

Because of the logical overlap with Controller Testing, `FilterTestTrait` is designed to work together with `ControllerTestTrait` should you need both on the same class. Once the trait has been included, `CIUnitTestCase` will detect its `setUp` method and prepare all the components needed for your tests. Should you need a special configuration, you can alter any of the properties before calling the support methods:

- `$request` A prepared version of the default `IncomingRequest` service
- `$response` A prepared version of the default `ResponseInterface` service
- `$filtersConfig` The default `Config\Filters` configuration (note: discovery is handled by `Filters` so this will not include module aliases)
- `$filters` An instance of `CodeIgniter\Filters\Filters` using the three components above
- `$collection` A prepared version of `RouteCollection` which includes the discovery of `Config\Routes`

The default configuration will usually be best for your testing since it most closely emulates a "live" project, but (for example) if you wanted to simulate a filter triggering accidentally on an unfiltered route, you could add it to the Config:

```php
<?php

namespace App\Filters;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FilterTestTrait;

final class FooFilterTest extends CIUnitTestCase
{
    use FilterTestTrait;

    protected function testFilterFailsOnAdminRoute()
    {
        $this->filtersConfig->globals['before'] = ['admin-only-filter'];

        $this->assertHasFilters('unfiltered/route', 'before');
    }

    // ...
}
```

### Checking Routes

The first helper method is `getFiltersForRoute()`, which will simulate the provided route and return a list of all Filters (by their alias) that would have run for the given position ("before" or "after"), without actually executing any controller or routing code. This has a large performance advantage over Controller and HTTP Testing.

#### getFiltersForRoute($route, $position)

- **Parameters**  
    - **$route** `string` The URI to check
    - **$position** `string` The filter method to check, "before" or "after"

- **Returns**: Aliases for each filter that would have run

- **Return type**: `string[]`

Usage example:

```php
<?php

$result = $this->getFiltersForRoute('/', 'after'); // ['toolbar']
```

### Calling Filter Methods

The properties described in Configuration are all set up to ensure maximum performance without interference from other tests. The next helper method will return a callable method using these properties to test your Filter code safely and check the results.

#### getFilterCaller($filter, $position)

- **Parameters**  
    - **$filter** `FilterInterface|string` The filter instance, class, or alias
    - **$position** `string` The filter method to run, "before" or "after"

- **Returns**: A callable method to run the simulated Filter event

- **Return type**: `Closure`

Usage example:

```php
<?php

namespace App\Filters;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FilterTestTrait;

final class FooFilterTest extends CIUnitTestCase
{
    use FilterTestTrait;

    protected function testUnauthorizedAccessRedirects()
    {
        $caller = $this->getFilterCaller('permission', 'before');
        $result = $caller('MayEditWidgets');

        $this->assertInstanceOf('CodeIgniter\HTTP\RedirectResponse', $result);
    }
}
```

Notice how the `Closure` can take input parameters which are passed to your filter method.

### Assertions

In addition to the helper methods above, `FilterTestTrait` also comes with some assertions to streamline your test methods.

#### assertFilter()

The `assertFilter()` method checks that the given route at the given position uses the filter (by its alias):

```php
<?php

// Make sure users are logged in before checking their account
$this->assertFilter('users/account', 'before', 'login');
```

#### assertNotFilter()

The `assertNotFilter()` method checks that the given route at the given position does not use the filter (by its alias):

```php
<?php

// Make sure API calls do not try to use the Debug Toolbar
$this->assertNotFilter('api/v1/widgets', 'after', 'toolbar');
```

#### assertHasFilters()

The `assertHasFilters()` method checks that the given route at the given position has at least one filter set:

```php
<?php

// Make sure that filters are enabled
$this->assertHasFilters('filtered/route', 'after');
```

#### assertNotHasFilters()

The `assertNotHasFilters()` method checks that the given route at the given position has no filters set:

```php
<?php

// Make sure no filters run for our static pages
$this->assertNotHasFilters('about/contact', 'before');
```
