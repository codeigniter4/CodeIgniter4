# Controller Filters

Controller Filters allow you to perform actions either before or after the controllers execute. Unlike [events](../extending/events.md), you can choose the specific URIs or routes to which the filters will be applied. Before filters may modify the Request while after filters can act on and even modify the Response, allowing for a lot of flexibility and power.

Some common examples of tasks that might be performed with filters are:

- Performing CSRF protection on the incoming requests
- Restricting areas of your site based upon their Role
- Performing rate limiting on certain endpoints
- Displaying a "Down for Maintenance" page
- Performing automatic content negotiation
- and more...

## Creating a Filter

Filters are simple classes that implement `CodeIgniter\Filters\FilterInterface`. They contain two methods: `before()` and `after()`, which hold the code that will run before and after the controller respectively. Your class must contain both methods but may leave the methods empty if they are not needed. A skeleton filter class looks like:

```php
<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class MyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Do something here
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
```

### Before Filters

#### Replacing Request

From any filter, you can return the `$request` object and it will replace the current Request, allowing you to make changes that will still be present when the controller executes.

#### Stopping Later Filters

When you have a series of filters, you may also want to stop the execution of the later filters after a certain filter. You can easily do this by returning **any non-empty** result. If the before filter returns an empty result, the controller actions or the later filters will still be executed.

An exception to the non-empty result rule is the `Request` instance. Returning it in the before filter will not stop the execution but only replace the current `$request` object.

#### Returning Response

Since before filters are executed prior to your controller being executed, you may at times want to stop the actions in the controller from happening.

This is typically used to perform redirects, like in this example:

```php
<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class MyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = service('auth');

        if (! $auth->isLoggedIn()) {
            return redirect()->to(site_url('login'));
        }
    }
}
```

If a `Response` instance is returned, the Response will be sent back to the client and script execution will stop. This can be useful for implementing rate limiting for APIs. See [Throttler](../libraries/throttler.md) for an example.

### After Filters

After filters are nearly identical to before filters, except that you can only return the `$response` object, and you cannot stop script execution. This does allow you to modify the final output, or simply do something with the final output. This could be used to ensure certain security headers were set the correct way, or to cache the final output, or even to filter the final output with a bad words filter.

## Configuring Filters

There are two ways to configure filters when they get run. One is done in **app/Config/Filters.php**, and the other is done in **app/Config/Routes.php**.

If you want to specify filters for defined routes, use **app/Config/Routes.php** and see [URI Routing](routing.md#applying-filters).

!!! note "Note"
    The safest way to apply filters is to [disable auto-routing](routing.md#use-defined-routes-only), and [set filters to routes](routing.md#applying-filters).

### app/Config/Filters.php

The **app/Config/Filters.php** file contains four properties that allow you to configure exactly when the filters run.

!!! warning "Warning"
    It is recommended that you always add `*` at the end of a URI in the filter settings, because a controller method might be accessible by different URLs than you think. For example, when [Auto Routing (Legacy)](routing.md#auto-routing-legacy) is enabled, if you have `Blog::index()`, it can be accessed with `blog`, `blog/index`, `blog/index/1`, etc.

#### $aliases

The `$aliases` array is used to associate a simple name with one or more fully-qualified class names that are the filters to run:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public array $aliases = [
        'csrf' => \CodeIgniter\Filters\CSRF::class,
    ];

    // ...
}
```

Aliases are mandatory, and if you try to use a full class name later, the system will throw an error.

Defining them in this way makes it simple to switch out the class used. This is great for when you decide you need to change to a different authentication system, since you only change the filter's class and you're done.

You can combine multiple filters into one alias, making complex sets of filters simple to apply:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public array $aliases = [
        'api-prep' => [
            \App\Filters\Negotiate::class,
            \App\Filters\ApiAuth::class,
        ],
    ];

    // ...
}
```

You should define as many aliases as you need.

#### $required

!!! success "Available from version 4.5.0"

The second section allows you to define **Required Filters**. They are special filters that are applied to every request made by the framework. They are applied before and after other kinds of filters that are explained below.

!!! note "Note"
    The Required Filters are always executed. However, if the route does not exist, only the Before Filters are executed.

You should take care with how many you use here, since it could have performance implications to have too many run on every request. But the filters set by default provide framework functionality. If removed, those functions will no longer work. See [Provided Filters](#provided-filters) for details.

Filters can be specified by adding their alias to either the `before` or `after` array:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    // ...
    public array $required = [
        'before' => [
            'forcehttps', // Force Global Secure Requests
            'pagecache',  // Web Page Caching
        ],
        'after' => [
            'pagecache',   // Web Page Caching
            'performance', // Performance Metrics
            'toolbar',     // Debug Toolbar
        ],
    ];

    // ...
}
```

#### $globals

The third section allows you to define any filters that should be applied to every valid request made by the framework.

You should take care with how many you use here, since it could have performance implications to have too many run on every request.

Filters can be specified by adding their alias to either the `before` or `after` array:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    // ...

    public array $globals = [
        'before' => [
            'csrf',
        ],
        'after' => [],
    ];

    // ...
}
```

##### Except for a Few URIs

There are times when you want to apply a filter to almost every request, but have a few that should be left alone. One common example is if you need to exclude a few URIs from the CSRF protection filter to allow requests from third-party websites to hit one or two specific URIs, while keeping the rest of them protected.

To do this, add an array with the `except` key and a URI path (relative to BaseURL) to match as the value alongside the alias:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    // ...

    public array $globals = [
        'before' => [
            'csrf' => ['except' => 'api/*'],
        ],
        'after' => [],
    ];

    // ...
}
```

!!! warning "Warning"
    Prior to v4.4.7, due to a bug, the URI paths processed by the filter were not URL-decoded. In other words, the URI paths specified in the routing and the URI paths specified in the filter could be different. See [Paths in Controller Filters](../installation/upgrade_447.md#paths-in-controller-filters) for details.

Any place you can use a URI path (relative to BaseURL) in the filter settings, you can use a regular expression or, as in the example above, use an asterisk (`*`) for a wildcard that will match all characters after that. In this example, any URI path starting with `api/` would be exempted from CSRF protection, but the site's forms would all be protected.

If you need to specify multiple URI paths, you can use an array of URI path patterns:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    // ...

    public array $globals = [
        'before' => [
            'csrf' => ['except' => ['foo/*', 'bar/*']],
        ],
        'after' => [],
    ];

    // ...
}
```

#### $methods

!!! warning "Warning"
    If you use `$methods` filters, you should [disable Auto Routing (Legacy)](routing.md#use-defined-routes-only) because [Auto Routing (Legacy)](routing.md#auto-routing-legacy) permits any HTTP method to access a controller. Accessing the controller with a method you don't expect could bypass the filter.

You can apply filters to all requests of a certain HTTP method, like `POST`, `GET`, `PUT`, etc. Its value would be an array of filters to run:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    // ...

    public array $methods = [
        'POST' => ['invalidchars', 'csrf'],
        'GET'  => ['csrf'],
    ];

    // ...
}
```

!!! note "Note"
    Unlike the `$globals` or the `$filters` properties, these will only run as before filters.

In addition to the standard HTTP methods, this also supports one special case: `CLI`. The `CLI` method would apply to all requests that were run from the command line.

!!! note "Note"
    Prior to v4.5.0, due to a bug, you needed to specify the HTTP method names in **lowercase**.

#### $filters

This property is an array of filter aliases. For each alias, you can specify `before` and `after` arrays that contain a list of URI path (relative to BaseURL) patterns that the filter should apply to:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    // ...

    public array $filters = [
        'foo' => ['before' => ['admin/*'], 'after' => ['users/*']],
        'bar' => ['before' => ['api/*', 'admin/*']],
    ];

    // ...
}
```

!!! warning "Warning"
    Prior to v4.4.7, due to a bug, the URI paths processed by the filter were not URL-decoded. In other words, the URI paths specified in the routing and the URI paths specified in the filter could be different. See [Paths in Controller Filters](../installation/upgrade_447.md#paths-in-controller-filters) for details.

##### Filter Arguments

!!! success "Available from version 4.4.0"

When configuring `$filters`, additional arguments may be passed to a filter:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    // ...

    public $filters = [
        'group:admin,superadmin'  => ['before' => ['admin/*']],
        'permission:users.manage' => ['before' => ['admin/users/*']],
    ];

    // ...
}
```

In this example, when the URI matches `admin/*`, the array `['admin', 'superadmin']` will be passed in `$arguments` to the `group` filter's `before()` method. When the URI matches `admin/users/*`, the array `['users.manage']` will be passed in `$arguments` to the `permission` filter's `before()` method.

!!! note "Note"
    Prior to v4.6.0, the same filter could not be run multiple times with different arguments.

### Filter Execution Order

!!! important "Important"
    Starting with v4.5.0, the order in which filters are executed has changed. If you wish to maintain the same execution order as in previous versions, you must set `Config\Feature::$oldFilterOrder` to `true`.

Filters are executed in the following order:

- **Before Filters**: required → globals → methods → filters → route
- **After Filters**: route → filters → globals → required

!!! note "Note"
    The *required* filters can be used since v4.5.0.

!!! note "Note"
    Prior to v4.5.0, the filters specified for a route (in **app/Config/Routes.php**) were executed before the filters specified in **app/Config/Filters.php**. And the execution order of the After Filters in Route filters and Filters filters was not reversed. See [Upgrading Guide](../installation/upgrade_450.md#filter-execution-order) for details.

## Confirming Filters

CodeIgniter has the following [command](../cli/spark_commands.md) to check the filters for a route.

### filter:check

!!! success "Available from version 4.3.0"

For example, check the filters for the route `/` with the **GET** method:

```console
php spark filter:check get /
```

The output is like the following:

```none
+--------+-------+----------------------+-------------------------------+
| Method | Route | Before Filters       | After Filters                 |
+--------+-------+----------------------+-------------------------------+
| GET    | /     | forcehttps pagecache | pagecache performance toolbar |
+--------+-------+----------------------+-------------------------------+

Before Filter Classes:
CodeIgniter\Filters\ForceHTTPS → CodeIgniter\Filters\PageCache
After Filter Classes:
CodeIgniter\Filters\PageCache → CodeIgniter\Filters\PerformanceMetrics → CodeIgniter\Filters\DebugToolbar
```

!!! note "Note"
    Since v4.6.0, filter arguments are displayed in the output table. Also, the actual filter classnames are displayed at the end.

You can also see the routes and filters with the `spark routes` command, but it might not show accurate filters when you use regular expressions for routes. See [URI Routing](routing.md#spark-routes) for details.

## Provided Filters

The filters bundled with CodeIgniter4 are:

- `cors` => [Cross-Origin Resource Sharing (CORS)](../libraries/cors.md)
- `csrf` => [CSRF](../libraries/security.md#cross-site-request-forgery-csrf)
- `toolbar` => [DebugToolbar](../testing/debugging.md#the-debug-toolbar)
- `honeypot` => [Honeypot](../libraries/honeypot.md)
- `invalidchars` => [InvalidChars](#invalidchars)
- `secureheaders` => [SecureHeaders](#secureheaders)
- `forcehttps` => [ForceHTTPS](#forcehttps)
- `pagecache` => [PageCache](../general/caching.md)
- `performance` => [PerformanceMetrics](#performancemetrics)

!!! note "Note"
    The filters are executed in the order defined in the config file. However, if enabled, `DebugToolbar` is always executed last because it should be able to capture everything that happens in the other filters.

### ForceHTTPS

!!! success "Available from version 4.5.0"

This filter provides the "Force Global Secure Requests" feature.

If you set `Config\App::$forceGlobalSecureRequests` to true, this will force every request made to this application to be made via a secure connection (HTTPS). If the incoming request is not secure, the user will be redirected to a secure version of the page and the HTTP Strict Transport Security (HSTS) header will be set.

### PerformanceMetrics

!!! success "Available from version 4.5.0"

This filter provides the pseudo-variables for performance metrics.

If you would like to display the total elapsed time from the moment CodeIgniter starts to the moment right before the final output is sent to the browser, simply place this pseudo-variable in one of your views:

```
{elapsed_time}
```

If you would like to show your memory usage in your view files, use this pseudo-variable:

```
{memory_usage}
```

If you don't need this feature, remove `'performance'` from `$required['after']`.

### InvalidChars

This filter prohibits user input data (`$_GET`, `$_POST`, `$_COOKIE`, `php://input`) from containing the following characters:

- invalid UTF-8 characters
- control characters except line break and tab code

### SecureHeaders

This filter adds HTTP response headers that your application can use to increase the security of your application.

If you want to customize the headers, extend `CodeIgniter\Filters\SecureHeaders` and override the `$headers` property. And change the `$aliases` property in **app/Config/Filters.php**:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public array $aliases = [
        // ...
        'secureheaders' => \App\Filters\SecureHeaders::class,
    ];

    // ...
}
```

If you want to know about secure headers, see [OWASP Secure Headers Project](https://owasp.org/www-project-secure-headers/).
