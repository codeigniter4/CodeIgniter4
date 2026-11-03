# Controller Attributes

PHP Attributes can be used to define filters and other metadata on controller classes and methods. This keeps the configuration close to the code it affects, and can make it easier to see at a glance what filters are applied to a given controller or method. This works across all routing methods, including auto-routing, which allows for near feature parity between the more robust route declarations and auto-routing.

## Getting Started

Controller Attributes can be applied to either the entire class or a specific method. The following example shows how to apply the `Filter` attribute to a controller class:

```php
<?php

namespace App\Controllers;

use CodeIgniter\Router\Attributes\Filter;

#[Filter(by: 'auth')]
class AdminController extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }
}
```

In this example, the `Auth` filter will be applied to all methods in `AdminController`.

You can also apply the `Filter` attribute to a specific method within a controller. This allows you to apply filters only to certain methods, while leaving others unaffected. Here's an example:

```php
<?php

namespace App\Controllers;

use CodeIgniter\Router\Attributes\Filter;

#[Filter(by: 'group', having: ['admin', 'superadmin'])]
class AdminController extends BaseController
{
    #[Filter(by: 'permission', having: ['users.manage'])]
    public function users()
    {
        // Will have 'group' filter with ['admin', 'superadmin']
        // and 'permission' filter with ['users.manage']
    }
}
```

Class-level and method-level attributes can work together to provide a flexible way to manage your routes at the controller level.

### Disabling Attributes

If you know that you will not be using attributes in your application, you can disable the feature by setting the `$useControllerAttributes` property in your `app/Config/Routing.php` file to `false`.

## Provided Attributes

### Filter

The repeatable `Filter` attribute applies a filter to a controller class or method. You can also provide arguments to the filter. Repeat the attribute to apply multiple filters:

```php
<?php

namespace App\Controllers;

use CodeIgniter\Router\Attributes\Filter;

class HomeController extends BaseController
{
    // Apply the filter by it's alias name
    #[Filter(by: 'csrf')]
    public function index()
    {
    }

    // Apply a filter with arguments
    #[Filter(by: 'throttle', having: ['60', '1'])]
    public function api()
    {
    }

    // Multiple filters can be applied by repeating the attribute
    #[Filter(by: 'auth')]
    #[Filter(by: 'csrf')]
    public function admin()
    {
    }
}
```

!!! note "Note"
    When filters are applied both by an attribute and in the filter configuration file, they will both be applied, but that could lead to unexpected results.

!!! note "Note"
    Please remember that every parameter applied to the filter will be converted to a string. This behavior affects only filters.

### Restrict

The `Restrict` attribute allows you to restrict access to the class or method based on the domain, the sub-domain, or the environment the application is running in. Here's an example of how to use the `Restrict` attribute:

```php
<?php

namespace App\Controllers;

use CodeIgniter\Router\Attributes\Restrict;

// Restrict access by environment
#[Restrict(environment: ['development', '!production'])]
class HomeController extends BaseController
{
    // Restrict access by hostname
    #[Restrict(hostname: 'localhost')]
    public function index()
    {
    }

    // Multiple allowed hosts
    #[Restrict(hostname: ['localhost', '127.0.0.1', 'dev.example.com'])]
    public function devOnly()
    {
    }

    // Restrict to subdomain, e.g. admin.example.com
    #[Restrict(subdomain: 'admin')]
    public function deleteItem($id)
    {
    }
}
```

### Cache

The `Cache` attribute allows the output of the controller method to be cached for a specified amount of time. You can specify a duration in seconds, and optionally a cache key. Here's an example of how to use the `Cache` attribute:

```php
<?php

namespace App\Controllers;

use CodeIgniter\Router\Attributes\Cache;

class HomeController extends BaseController
{
    // Cache this method's response for 2 hours
    #[Cache(for: 2 * HOUR)]
    public function index()
    {
        return view('welcome_message');
    }

    // Custom cache key
    #[Cache(for: 10 * MINUTE, key: 'custom_cache_key')]
    public function custom()
    {
        return 'This response is cached with a custom key for 10 minutes.';
    }
}
```

## Custom Attributes

You can also create your own custom attributes to add metadata or behavior to your controllers and methods. Custom attributes must implement the `CodeIgniter\Router\Attributes\RouteAttributeInterface` interface. Here's an example of a custom attribute that adds a custom header to the response:

```php
<?php

namespace App\Attributes;

use Attribute;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Router\Attributes\RouteAttributeInterface;

/**
 * Custom Header Attribute
 *
 * Adds custom headers to the response. This is useful for:
 * - Adding security headers
 * - Setting API version information
 * - Adding custom metadata to responses
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AddHeader implements RouteAttributeInterface
{
    /**
     * @param string $name  The header name
     * @param string $value The header value
     */
    public function __construct(
        private readonly string $name,
        private readonly string $value,
    ) {
    }

    /**
     * Called before the controller method executes.
     * Return null to continue to the controller.
     */
    public function before(RequestInterface $request): RequestInterface|ResponseInterface|null
    {
        // We don't need to do anything before the controller runs
        return null;
    }

    /**
     * Called after the controller method executes.
     * Add the custom header to the response.
     */
    public function after(RequestInterface $request, ResponseInterface $response): ?ResponseInterface
    {
        $response->setHeader($this->name, $this->value);

        return $response;
    }
}
```

You can then apply this custom attribute to a controller class or method just like the built-in attributes:

```php
<?php

namespace App\Controllers;

use App\Attributes\AddHeader;
use CodeIgniter\Controller;
use CodeIgniter\Router\Attributes\Cache;

class Api extends Controller
{
    /**
     * Add a single custom header
     */
    #[AddHeader('X-API-Version', '2.0')]
    public function userInfo()
    {
        return $this->response->setJSON([
            'name'  => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    /**
     * Add multiple custom headers using the IS_REPEATABLE attribute option.
     * Each AddHeader attribute will be executed in order.
     */
    #[AddHeader('X-API-Version', '2.0')]
    #[AddHeader('X-Rate-Limit', '100')]
    #[AddHeader('X-Content-Source', 'cache')]
    public function statistics()
    {
        return $this->response->setJSON([
            'users' => 1500,
            'posts' => 3200,
        ]);
    }

    /**
     * Combine custom attributes with built-in attributes.
     * The Cache attribute will cache the response,
     * and AddHeader will add the custom header.
     */
    #[AddHeader('X-Powered-By', 'My Custom API')]
    #[Cache(for: 3600)]
    public function dashboard()
    {
        return $this->response->setJSON([
            'status' => 'operational',
            'uptime' => '99.9%',
        ]);
    }
}
```
