# Cross-Origin Resource Sharing (CORS)

!!! success "Available from version 4.5.0"

Cross-Origin Resource Sharing (CORS) is an HTTP-header based security mechanism that allows a server to indicate any origins (domain, scheme, or port) other than its own from which a browser should permit loading resources.

CORS works by adding headers to HTTP requests and responses to indicate whether the requested resource can be shared across different origins, helping to prevent malicious attacks like cross-site request forgery (CSRF) and data theft.

If you are not familiar with CORS and CORS headers, please read the [MDN documentation on CORS](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS#the_http_response_headers).

CodeIgniter provides the CORS filter and helper class.

## Configuring CORS

### Setting Default Config

CORS can be configured in **app/Config/Cors.php**.

At a minimum, the following items in the `$default` property must be set:

- `allowedOrigins`: List explicitly the Origin(s) you want to allow.
- `allowedHeaders`: List explicitly the HTTP headers you want to allow.
- `allowedMethods`: List explicitly the HTTP methods you want to allow.

!!! warning "Warning"
    Based on the principle of least privilege, only the minimum necessary Origin, Methods, and Headers should be allowed.

If you send credentials (e.g., cookies) with a cross-origin request, set `supportsCredentials` to `true`.

### Enabling CORS

To enable CORS, you need to do two things:

1.  Set the `cors` filter on the routes that permit CORS.
2.  Add **OPTIONS** routes for CORS Preflight Requests.

#### Set against Routes

You can set the `cors` filter on routes in **app/Config/Routes.php**.

E.g.,

```php
<?php

use CodeIgniter\Router\RouteCollection;

$routes->group('', ['filter' => 'cors'], static function (RouteCollection $routes): void {
    $routes->resource('product');

    $routes->options('product', static function () {
        // Implement processing for normal non-preflight OPTIONS requests,
        // if necessary.
        $response = response();
        $response->setStatusCode(204);
        $response->setHeader('Allow:', 'OPTIONS, GET, POST, PUT, PATCH, DELETE');

        return $response;
    });
    $routes->options('product/(:any)', static function () {});
});
```

Don't forget to add OPTIONS routes for Preflight Requests, because Controller Filters (except for Required Filters) do not work if the route does not exist.

The CORS filter handles all Preflight Requests, so the closure controllers for the OPTIONS routes are not normally called.

#### Set in Config\Filters

Alternatively, you can set the `cors` filter on URI paths in **app/Config/Filters.php**.

E.g.,

```php
<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;

// ...

class Filters extends BaseFilters
{
    // ...
    public array $filters = [
        // ...
        'cors' => [
            'before' => ['api/*'],
            'after'  => ['api/*'],
        ],
    ];
}
```

Don't forget to add OPTIONS routes for Preflight Requests, because Controller Filters (except for Required Filters) do not work if the route does not exist.

E.g.,

```php
<?php

use CodeIgniter\Router\RouteCollection;

$routes->group('', ['filter' => 'cors'], static function (RouteCollection $routes): void {
    $routes->options('api/(:any)', static function () {});
});
```

The CORS filter handles all Preflight Requests, so the closure controller for the OPTIONS routes is not normally called.

### Checking Routes and Filters

After configuration, you can check the routes and filters with the [spark routes](../incoming/routing.md#spark-routes) command.

### Setting Another Config

If you want to use a different configuration than the default configuration, add a property to **app/Config/Cors.php**.

For example, add the `$api` property.

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Cross-Origin Resource Sharing (CORS) Configuration
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
 */
class Cors extends BaseConfig
{
    // ...

    public array $api = [
        'allowedOrigins'         => ['https://app.example.com'],
        'allowedOriginsPatterns' => [],
        'supportsCredentials'    => true,
        'allowedHeaders'         => ['Authorization', 'Content-Type'],
        'exposedHeaders'         => [],
        'allowedMethods'         => ['GET', 'POST', 'PUT', 'DELETE'],
        'maxAge'                 => 7200,
    ];
}
```

The property name (`api` in the above example) will become the configuration name.

Then, specify the property name as the filter argument like `cors:api`:

```php
<?php

use CodeIgniter\Router\RouteCollection;

$routes->group('api', ['filter' => 'cors:api'], static function (RouteCollection $routes): void {
    $routes->resource('user');

    $routes->options('user', static function () {});
    $routes->options('user/(:any)', static function () {});
});
```

You can also use [Filter Arguments](../incoming/filters.md#filter-arguments).

## Class Reference

## CodeIgniter\HTTP

### Cors

#### addResponseHeaders(RequestInterface $request, ResponseInterface $response): ResponseInterface

- **Parameters**  
    - **$request** `RequestInterface` Request instance
    - **$response** `ResponseInterface` Response instance

- **Returns**: Response instance

- **Return type**: `ResponseInterface`

Adds response headers for CORS.

#### handlePreflightRequest(RequestInterface $request, ResponseInterface $response): ResponseInterface

- **Parameters**  
    - **$request** `RequestInterface` Request instance
    - **$response** `ResponseInterface` Response instance

- **Returns**: Response instance

- **Return type**: `ResponseInterface`

Handles Preflight Requests.

#### isPreflightRequest(IncomingRequest $request): bool

- **Parameters**  
    - **$request** `IncomingRequest` Request instance

- **Returns**: True if it is a Preflight Request.

- **Return type**: `bool`

Checks if the request is a Preflight Request.
