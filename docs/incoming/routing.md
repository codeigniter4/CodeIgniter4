# URI Routing

## What is URI Routing?

URI Routing associates a URI with a controller's method.

CodeIgniter has two kinds of routing. One is **Defined Route Routing**, and the other is **Auto Routing**. With Defined Route Routing, you can define routes manually. It allows flexible URLs. Auto Routing automatically routes HTTP requests based on conventions and executes the corresponding controller methods. There is no need to define routes manually.

First, let's look at Defined Route Routing. If you want to use Auto Routing, see [Auto Routing (Improved)](auto_routing_improved.md#auto-routing-improved).

## Setting Routing Rules

Routing rules are defined in the **app/Config/Routes.php** file. In it you'll see that it creates an instance of the RouteCollection class (`$routes`) that permits you to specify your own routing criteria. Routes can be specified using placeholders or Regular Expressions.

When you specify a route, you choose a method corresponding to HTTP verbs (request method). If you expect a GET request, you use the `get()` method:

```php
<?php

$routes->get('/', 'Home::index');
```

A route takes the **Route Path** (the URI path relative to the BaseURL, here `/`) on the left, and maps it to the **Route Handler** (the controller and method, here `Home::index`) on the right, along with any parameters that should be passed to the controller.

The controller and method should be listed in the same way that you would use a static method, by separating the class and its method with a double-colon, like `Users::list`.

If that method requires parameters to be passed to it, then they would be listed after the method name, separated by forward slashes:

```php
<?php

// Calls $Users->list()
$routes->get('users', 'Users::list');

// Calls $Users->list(1, 23)
$routes->get('users/1/23', 'Users::list/1/23');
```

### Examples

Here are a few basic routing examples.

A URL containing the word **journals** in the first segment will be mapped to the `\App\Controllers\Blogs` class, and the [default method](#default-method), which is usually `index()`:

```php
<?php

$routes->get('journals', 'Blogs');
```

A URL containing the segments **blog/joe** will be mapped to the `\App\Controllers\Blogs` class and the `users()` method. The ID will be set to `34`:

```php
<?php

$routes->get('blog/joe', 'Blogs::users/34');
```

A URL with **product** as the first segment and anything in the second will be mapped to the `\App\Controllers\Catalog` class and the `productLookup()` method:

```php
<?php

$routes->get('product/(:segment)', 'Catalog::productLookup');
```

A URL with **product** as the first segment and a number in the second will be mapped to the `\App\Controllers\Catalog` class and the `productLookupByID()` method, passing in the match as a variable to the method:

```php
<?php

$routes->get('product/(:num)', 'Catalog::productLookupByID/$1');
```

### HTTP verb Routes

You can use any standard HTTP verb (GET, POST, PUT, DELETE, OPTIONS, etc.):

```php
<?php

$routes->post('products', 'Product::feature');
$routes->put('products/1', 'Product::feature');
$routes->delete('products/1', 'Product::feature');
```

You can supply multiple verbs that a route should match by passing them in as an array to the `match()` method:

```php
<?php

$routes->match(['GET', 'PUT'], 'products', 'Product::feature');
```

### Specifying Route Handlers

#### Controller's Namespace

When you specify a controller and method name as a string, if a controller is written without a leading `\`, the [Default Namespace](#default-namespace) will be prepended:

```php
<?php

// Routes to \App\Controllers\Api\Users::update()
$routes->post('api/users', 'Api\Users::update');
```

If you put `\` at the beginning, it is treated as a fully qualified class name:

```php
<?php

// Routes to \Acme\Blog\Controllers\Home::list()
$routes->get('blog', '\Acme\Blog\Controllers\Home::list');
```

You can also specify the namespace with the `namespace` option:

```php
<?php

// Routes to \Admin\Users::index()
$routes->get('admin/users', 'Users::index', ['namespace' => 'Admin']);
```

See [Assigning Namespace](#assigning-namespace) for details.

#### Array Callable Syntax

!!! success "Available from version 4.2.0"

Since v4.2.0, you can use array callable syntax to specify the controller:

```php
$routes->get('/', [\App\Controllers\Home::class, 'index']);
```

Or using the `use` keyword:

```php
use App\Controllers\Home;

$routes->get('/', [Home::class, 'index']);
```

If you forget to add `use App\Controllers\Home;`, the controller classname is interpreted as `\Home`, not `App\Controllers\Home`.

!!! note "Note"
    When you use Array Callable Syntax, the classname is always interpreted as a fully qualified classname. So [Default Namespace](#default-namespace) and [namespace option](#assigning-namespace) have no effect.

##### Array Callable Syntax and Placeholders

If there are placeholders, it will automatically set the parameters in the specified order:

```php
use App\Controllers\Product;

$routes->get('product/(:num)/(:num)', [Product::class, 'index']);

// The above code is the same as the following:
$routes->get('product/(:num)/(:num)', 'Product::index/$1/$2');
```

But the auto-configured parameters may not be correct if you use regular expressions in routes. In such a case, you can specify the parameters manually:

```php
use App\Controllers\Product;

$routes->get('product/(:num)/(:num)', [[Product::class, 'index'], '$2/$1']);

// The above code is the same as the following:
$routes->get('product/(:num)/(:num)', 'Product::index/$2/$1');
```

#### Using Closures

You can use an anonymous function, or Closure, as the destination that a route maps to. This function will be executed when the user visits that URI. This is handy for quickly executing small tasks, or even just showing a simple view:

```php
<?php

use App\Libraries\RSSFeeder;

$routes->get('feed', static function () {
    $rss = new RSSFeeder();

    return $rss->feed('general');
});
```

### Specifying Route Paths

#### Placeholders

A typical route might look something like this:

```php
<?php

$routes->get('product/(:num)', 'Catalog::productLookup');
```

In a route, the first parameter contains the URI to be matched, while the second parameter contains the destination it should be routed to. In the above example, if the literal word "product" is found in the first segment of the URL path, and a number is found in the second segment, the `Catalog` class and the `productLookup` method are used instead.

Placeholders are simply strings that represent a Regular Expression pattern. During the routing process, these placeholders are replaced with the value of the Regular Expression. They are primarily used for readability.

The following placeholders are available for you to use in your routes:

| Placeholders | Description |
|----|----|
| (:any) | will match all characters from that point to the end of the URI. This may include multiple URI segments. |
| (:segment) | will match any character except for a forward slash (`/`) restricting the result to a single segment. |
| (:num) | will match any positive integer. |
| (:alpha) | will match any string of alphabetic characters. |
| (:alphanum) | will match any string of alphabetic characters or integers, or any combination of the two. |
| (:hash) | is the same as `(:segment)`, but can be used to easily see which routes use hashed ids. |

!!! note "Note"
    `{locale}` cannot be used as a placeholder or other part of the route, as it is reserved for use in [localization](../outgoing/localization.md).

##### The Behavior of (:any)

Note that a single `(:any)` will match multiple segments in the URL if present.

For example, the route:

```php
<?php

$routes->get('product/(:any)', 'Catalog::productLookup/$1');
```

will match **product/123**, **product/123/456**, **product/123/456/789** and so on.

By default, in the above example, if the `$1` placeholder contains a slash (`/`), it will still be split into multiple parameters when passed to `Catalog::productLookup()`.

!!! note "Note"
    Since v4.5.0, you can change the behavior with the config option. See [Multiple URI Segments as One Parameter](#multiple-uri-segments-as-one-parameter) for details.

The implementation in the Controller should take into account the maximum parameters:

```php
<?php

namespace App\Controllers;

class Catalog extends BaseController
{
    public function productLookup($seg1 = false, $seg2 = false, $seg3 = false)
    {
        echo $seg1; // Will be 123 in all examples
        echo $seg2; // false in first, 456 in second and third example
        echo $seg3; // false in first and second, 789 in third
    }
}
```

Or you can use [variable-length argument lists](https://www.php.net/manual/en/functions.arguments.php#functions.variable-arg-list):

```php
<?php

namespace App\Controllers;

class Catalog extends BaseController
{
    public function productLookup(...$params)
    {
        echo $params[0] ?? null; // Will be 123 in all examples
        echo $params[1] ?? null; // null in first, 456 in second and third example
        echo $params[2] ?? null; // null in first and second, 789 in third
    }
}
```

!!! important "Important"
    Do not put any placeholder after `(:any)`, because the number of parameters passed to the controller method may change.

If matching multiple segments is not the intended behavior, `(:segment)` should be used when defining the routes. With the example URLs from above:

```php
<?php

$routes->get('product/(:segment)', 'Catalog::productLookup/$1');
```

will only match **product/123** and generate 404 errors for the other examples.

#### Custom Placeholders

You can create your own placeholders that can be used in your routes file to fully customize the experience and readability.

You add new placeholders with the `addPlaceholder()` method. The first parameter is the string to be used as the placeholder. The second parameter is the Regular Expression pattern it should be replaced with. This must be called before you add the route:

```php
<?php

$routes->addPlaceholder('uuid', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
$routes->get('users/(:uuid)', 'Users::show/$1');
```

#### Regular Expressions

If you prefer, you can use regular expressions to define your routing rules. Any valid regular expression is allowed, as are back-references.

!!! important "Important"
    If you use back-references, you must use the dollar syntax rather than the double backslash syntax. A typical RegEx route might look something like this:

    ```php
    <?php

    $routes->get('products/([a-z]+)/(\d+)', 'Products::show/$1/id_$2');
    ```

In the above example, a URI similar to **products/shirts/123** would instead call the `show()` method of the `Products` controller class, with the original first and second segments passed as arguments to it.

With regular expressions, you can also catch a segment containing a forward slash (`/`), which would usually represent the delimiter between multiple segments.

For example, if a user accesses a password-protected area of your web application and you wish to be able to redirect them back to the same page after they log in, you may find this example useful:

```php
<?php

$routes->get('login/(.+)', 'Auth::login/$1');
```

By default, in the above example, if the `$1` placeholder contains a slash (`/`), it will still be split into multiple parameters when passed to `Auth::login()`.

!!! note "Note"
    Since v4.5.0, you can change the behavior with the config option. See [Multiple URI Segments as One Parameter](#multiple-uri-segments-as-one-parameter) for details.

For those of you who don't know regular expressions and want to learn more about them, [regular-expressions.info](https://www.regular-expressions.info/) might be a good starting point.

!!! note "Note"
    You can also mix and match placeholders with regular expressions.

### View Routes

!!! success "Available from version 4.3.0"

If you just want to render a view that has no logic associated with it, you can use the `view()` method. This is always treated as a GET request. This method accepts the name of the view to load as the second parameter.

```php
<?php

// Displays the view in /app/Views/pages/about.php
$routes->view('about', 'pages/about');
```

If you use placeholders within your route, you can access them within the view in a special variable, `$segments`. They are available as an array, indexed in the order they appear in the route.

```php
<?php

// Displays the view in /app/Views/map.php
$routes->view('map/(:segment)/(:segment)', 'map');

// Within the view, you can access the segments with
// $segments[0] and $segments[1] respectively.
```

### Redirecting Routes

Any site that lives long enough is bound to have pages that move. You can specify routes that should redirect to other routes with the `addRedirect()` method. The first parameter is the URI pattern for the old route. The second parameter is either the new URI to redirect to, or the name of a named route. The third parameter is the HTTP status code that should be sent along with the redirect. The default value is `302`, which is a temporary redirect and is recommended in most cases:

```php
<?php

$routes->get('users/profile', 'Users::profile', ['as' => 'profile']);

// Redirect to a named route
$routes->addRedirect('users/about', 'profile');
// Redirect to a URI
$routes->addRedirect('users/about', 'users/profile');

// Redirect with placeholder
$routes->get('post/(:num)/comment/(:num)', 'PostsComments::index', ['as' => 'post.comment']);

// Redirect to a URI
$routes->addRedirect('article/(:num)/(:num)', 'post/$1/comment/$2');
// Redirect to a named route
$routes->addRedirect('article/(:num)/(:num)', 'post.comment');
```

!!! note "Note"
    Since v4.2.0, `addRedirect()` can use placeholders.

If a redirect route is matched during a page load, the user will be immediately redirected to the new page before a controller can be loaded.

### Environment Restrictions

You can create a set of routes that will only be viewable in a certain environment. This allows you to create tools that only the developer can use on their local machines that are not reachable on testing or production servers. This can be done with the `environment()` method. The first parameter is the name of the environment. Any routes defined within this closure are only accessible from the given environment:

```php
<?php

$routes->environment('development', static function ($routes) {
    $routes->get('builder', 'Tools\Builder::index');
});
```

### Routes with any HTTP verbs

!!! important "Important"
    This method exists only for backward compatibility. Do not use it in new projects. Even if you are already using it, we recommend that you use another, more appropriate method.

!!! warning "Warning"
    While the `add()` method seems to be convenient, it is recommended to always use the HTTP-verb-based routes, described above, as it is more secure. If you use the [CSRF protection](../libraries/security.md), it does not protect **GET** requests. If the URI specified in the `add()` method is accessible by the GET method, the CSRF protection will not work.

It is possible to define a route with any HTTP verb. You can use the `add()` method:

```php
<?php

$routes->add('products', 'Product::feature');
```

!!! note "Note"
    Using the HTTP-verb-based routes will also provide a slight performance increase, since only routes that match the current request method are stored, resulting in fewer routes to scan through when trying to find a match.

### Mapping Multiple Routes

!!! important "Important"
    This method exists only for backward compatibility. Do not use it in new projects. Even if you are already using it, we recommend that you use another, more appropriate method.

!!! warning "Warning"
    Like `add()`, the `map()` method is not recommended because it calls `add()` internally.

While the `add()` method is simple to use, it is often handier to work with multiple routes at once, using the `map()` method. Instead of calling the `add()` method for each route that you need to add, you can define an array of routes and then pass it as the first parameter to the `map()` method:

```php
<?php

$multipleRoutes = [
    'product/(:num)'      => 'Catalog::productLookupById',
    'product/(:alphanum)' => 'Catalog::productLookupByName',
];

$routes->map($multipleRoutes);
```

### Command-Line Only Routes

!!! note "Note"
    It is recommended to use Spark Commands for CLI scripts instead of calling controllers via CLI. See the [Creating Spark Commands](../cli/cli_commands.md) page for detailed information.

Any route created by any of the HTTP-verb-based route methods will also be inaccessible from the CLI, but routes created by the `add()` method will still be available from the command line.

You can create routes that work only from the command line, and are inaccessible from the web browser, with the `cli()` method:

```php
<?php

$routes->cli('migrate', 'App\Database::migrate');
```

!!! warning "Warning"
    If you enable [Auto Routing (Legacy)](#auto-routing-legacy) and place the command file in **app/Controllers**, anyone could access the command with the help of Auto Routing (Legacy) via HTTP.

## Global Options

All of the methods for creating a route (`get()`, `post()`, [resource()](restful.md), etc.) can take an array of options that can modify the generated routes, or further restrict them. The `$options` array is always the last parameter:

```php
<?php

$routes->add('from', 'to', $options);
$routes->get('from', 'to', $options);
$routes->post('from', 'to', $options);
$routes->put('from', 'to', $options);
$routes->head('from', 'to', $options);
$routes->options('from', 'to', $options);
$routes->delete('from', 'to', $options);
$routes->patch('from', 'to', $options);
$routes->match(['GET', 'PUT'], 'from', 'to', $options);
$routes->resource('photos', $options);
$routes->map($array, $options);
$routes->group('name', $options, static function () {});
```

### Applying Filters

You can alter the behavior of specific routes by supplying filters to run before or after the controller. This is especially handy during authentication or API logging.

The value for the filter can be a string or an array of strings:

- matching the aliases defined in **app/Config/Filters.php**.
- filter classnames

See [Controller Filters](filters.md#aliases) for more information on defining aliases.

!!! warning "Warning"
    If you set filters to routes in **app/Config/Routes.php** (not in **app/Config/Filters.php**), it is recommended to disable Auto Routing (Legacy). When [Auto Routing (Legacy)](#auto-routing-legacy) is enabled, it may be possible that a controller can be accessed via a different URL than the configured route, in which case the filter you specified for the route will not be applied. See [Use Defined Routes Only](#use-defined-routes-only) to disable auto-routing.

#### Alias Filter

You specify an alias [defined in app/Config/Filters.php](filters.md#aliases) for the filter value:

```php
<?php

$routes->get('admin', ' AdminController::index', ['filter' => 'admin-auth']);
```

You may also supply arguments to be passed to the alias filter's `before()` and `after()` methods:

```php
<?php

$routes->post('users/delete/(:segment)', 'AdminController::index', ['filter' => 'admin-auth:dual,noreturn']);
```

#### Classname Filter

!!! success "Available from version 4.1.5"

You can specify a filter classname for the filter value:

```php
<?php

$routes->get('admin', ' AdminController::index', ['filter' => \App\Filters\SomeFilter::class]);
```

#### Multiple Filters

!!! success "Available from version 4.1.5"

!!! important "Important"
    Since v4.5.0, *Multiple Filters* are always enabled. Prior to v4.5.0, *Multiple Filters* were disabled by default. If you want to use them prior to v4.5.0, see [Upgrading from 4.1.4 to 4.1.5](../installation/upgrade_415.md#multiple-filters-for-a-route) for the details.

You can specify an array for the filter value:

```php
<?php

$routes->get('admin', ' AdminController::index', ['filter' => ['admin-auth', \App\Filters\SomeFilter::class]]);
```

##### Filter Arguments

Additional arguments may be passed to a filter:

```php
<?php

$routes->add('users/delete/(:segment)', 'AdminController::index', ['filter' => 'admin-auth:dual,noreturn']);
```

In this example, the array `['dual', 'noreturn']` will be passed in `$arguments` to the filter's `before()` and `after()` implementation methods.

### Assigning Namespace

While a [Default Namespace](#default-namespace) will be prepended to the generated controllers, you can also specify a different namespace to be used in any options array, with the `namespace` option. The value should be the namespace you want modified:

```php
<?php

// Routes to \Admin\Users::index()
$routes->get('admin/users', 'Users::index', ['namespace' => 'Admin']);
```

The new namespace is only applied during that call for any methods that create a single route, like get, post, etc. For any methods that create multiple routes, the new namespace is attached to all routes generated by that function or, in the case of `group()`, all routes generated while in the closure.

### Limit to Hostname

You can restrict groups of routes to function only in certain domains or sub-domains of your application by passing the "hostname" option along with the desired domain to allow it on as part of the options array:

```php
<?php

$routes->get('from', 'to', ['hostname' => 'accounts.example.com']);
```

This example would only allow the specified hosts to work if the domain exactly matched **accounts.example.com**. It would not work under the main site at **example.com**.

!!! note "Note"
    The `hostname` option is compared against the full `HTTP_HOST` value of the request, including the port when one is present. When your application is served on a non-standard port (for example `localhost:8080`, behind Docker, or when using `php spark serve`), you must include the port in the `hostname` option for the routes to match. For example, `['hostname' => 'localhost:8080']` matches only requests to `localhost:8080`.

#### Restrict by Multiple Hostnames

!!! success "Available from version 4.6.0"

You can also restrict by multiple hostnames, e.g.:

```php
<?php

$routes->get('from', 'to', ['hostname' => ['s1.example.com', 's2.example.com']]);
```

### Limit to Subdomains

When the `subdomain` option is present, the system will restrict the routes to only be available on that sub-domain. The route will only be matched if the subdomain is the one the application is being viewed through:

```php
<?php

// Limit to media.example.com
$routes->get('from', 'to', ['subdomain' => 'media']);
```

You can restrict it to any subdomain by setting the value to an asterisk (`*`). If you are viewing from a URL that does not have any subdomain present, this will not be matched:

```php
<?php

// Limit to any sub-domain
$routes->get('from', 'to', ['subdomain' => '*']);
```

!!! important "Important"
    The system is not perfect and should be tested for your specific domain before being used in production. Most domains should work fine, but some edge cases, especially with a period in the domain itself (not used to separate suffixes or www) can potentially lead to false positives.

### Offsetting the Matched Parameters

You can offset the matched parameters in your route by any numeric value with the `offset` option, with the value being the number of segments to offset.

This can be beneficial when developing APIs with the first URI segment being the version number. It can also be used when the first parameter is a language string:

```php
<?php

$routes->get('users/(:num)', 'users/show/$1', ['offset' => 1]);

// Creates:
$routes['users/(:num)'] = 'users/show/$2';
```

## Reverse Routing

Reverse routing allows you to define the controller and method, as well as any parameters, that a link should go to, and have the router look up the current route to it. This allows route definitions to change without you having to update your application code. This is typically used within views to create links.

For example, if you have a route to a photo gallery that you want to link to, you can use the `url_to()` helper function to get the route that should be used. The first parameter is the fully qualified Controller and method, separated by a double colon (`::`), much like you would use when writing the initial route itself. Any parameters that should be passed to the route are passed in next:

```php
<?php

// The route is defined as:
$routes->get('users/(:num)/gallery/(:num)', 'Galleries::showUserGallery/$1/$2');

?>

<!-- Generate the URI to link to user ID 15, gallery 12: -->
<a href="<?= url_to('Galleries::showUserGallery', 15, 12) ?>">View Gallery</a>
<!-- Result: 'http://example.com/users/15/gallery/12' -->
```

## Named Routes

You can name routes to make your application less fragile. This applies a name to a route that can be called later, and even if the route definition changes, all of the links in your application built with `url_to()` will still work without you having to make any changes. A route is named by passing in the `as` option with the name of the route:

```php
<?php

// The route is defined as:
$routes->get('users/(:num)/gallery/(:num)', 'Galleries::showUserGallery/$1/$2', ['as' => 'user_gallery']);

?>

<!-- Generate the URI to link to user ID 15, gallery 12: -->
<a href="<?= url_to('user_gallery', 15, 12) ?>">View Gallery</a>
<!-- Result: 'http://example.com/users/15/gallery/12' -->
```

This has the added benefit of making the views more readable, too.

!!! note "Note"
    By default, all defined routes have names matching their paths, with placeholders replaced by their corresponding regular expressions. For example, if you define a route like `$routes->get('edit/(:num)', 'PostController::edit/$1');`, you can generate the corresponding URL using `route_to('edit/([0-9]+)', 12)`.

!!! warning "Warning"
    According to [Route Priority](#route-priority), if an unnamed route is defined first (e.g., `$routes->get('edit', 'PostController::edit');`) and another named route is defined later with the same name as the path of the first route (e.g., `$routes->get('edit/(:num)', 'PostController::edit/$1', ['as' => 'edit']);`), the second route will not be registered because its name will conflict with the automatically assigned name of the first route.

## Grouping Routes

You can group your routes under a common name with the `group()` method. The group name becomes a segment that appears prior to the routes defined inside of the group. This allows you to reduce the typing needed to build out an extensive set of routes that all share the opening string, like when building an admin area:

```php
<?php

$routes->group('admin', static function ($routes) {
    $routes->get('users', 'Admin\Users::index');
    $routes->get('blog', 'Admin\Blog::index');
});
```

This would prefix the **users** and **blog** URIs with **admin**, handling URLs like **admin/users** and **admin/blog**.

### Setting Namespace

If you need to assign options to a group, like [Assigning Namespace](#assigning-namespace), do it before the callback:

```php
<?php

$routes->group('api', ['namespace' => 'App\API\v1'], static function ($routes) {
    $routes->resource('users');
});
```

This would handle a resource route to the `App\API\v1\Users` controller with the **api/users** URI.

### Setting Filters

You can also use a specific [filter](filters.md) for a group of routes. This will always run the filter before or after the controller. This is especially handy during authentication or API logging:

```php
<?php

$routes->group('api', ['filter' => 'api-auth'], static function ($routes) {
    $routes->resource('users');
});
```

The value for the filter must match one of the aliases defined within **app/Config/Filters.php**.

!!! note "Note"
    Prior to v4.5.4, due to a bug, filters passed to the `group()` were not merged into the filters passed to the inner routes.

### Setting Other Options

At some point, you may want to group routes for the purpose of applying filters or other route config options like namespace, subdomain, etc. Without necessarily needing to add a prefix to the group, you can pass an empty string in place of the prefix and the routes in the group will be routed as though the group never existed but with the given route config options:

```php
<?php

$routes->group('', ['namespace' => 'Myth\Auth\Controllers'], static function ($routes) {
    $routes->get('login', 'AuthController::login', ['as' => 'login']);
    $routes->post('login', 'AuthController::attemptLogin');
    $routes->get('logout', 'AuthController::logout');
});
```

### Nesting Groups

It is possible to nest groups within groups for finer organization if you need it:

```php
<?php

$routes->group('admin', ['filter' => 'myfilter1:config'], static function ($routes) {
    $routes->get('/', 'Admin\Admin::index');

    $routes->group('users', ['filter' => 'myfilter2:region'], static function ($routes) {
        $routes->get('list', 'Admin\Users::list');
    });
});
```

This would handle the URL at **admin/users/list**.

The `filter` option passed to the outer `group()` is merged with the inner `group()` filter option. The above code runs `myfilter1:config` for the route `admin`, and `myfilter1:config` and `myfilter2:region` for the route `admin/users/list`.

!!! note "Note"
    Prior to v4.6.0, the same filter could not be run multiple times with different arguments.

Any other overlapping options passed to the inner `group()` will overwrite their values.

!!! note "Note"
    Prior to v4.5.0, due to a bug, options passed to the outer `group()` were not merged with the inner `group()` options.

## Route Priority

Routes are registered in the routing table in the order in which they are defined. This means that when a URI is accessed, the first matching route will be executed.

!!! warning "Warning"
    If a route path is defined more than once with different handlers, only the first defined route is registered.

You can check registered routes in the routing table by running the [spark routes](#spark-routes) command.

### Changing Route Priority

When working with modules, it can be a problem if the routes in the application contain wildcards. Then the module routes will not be processed correctly. You can solve this problem by lowering the priority of route processing using the `priority` option. The parameter accepts positive integers and zero. The higher the number specified in the `priority`, the lower the route priority in the processing queue:

```php
<?php

// First you need to enable processing of the routes queue by priority.
$routes->setPrioritize();

// Config\Routes
$routes->get('(.*)', 'Posts::index', ['priority' => 1]);

// Modules\Acme\Config\Routes
$routes->get('admin', 'Admin::index');

// The "admin" route will now be processed before the wildcard route.
```

To disable this functionality, you must call the method with the parameter `false`:

```php
<?php

$routes->setPrioritize(false);
```

!!! note "Note"
    By default, all routes have a priority of 0. Negative integers will be cast to the absolute value.

## Routes Configuration Options

The RouteCollection class provides several options that affect all routes, and can be modified to meet your application's needs. These options are available in **app/Config/Routing.php**.

!!! note "Note"
    The config file **app/Config/Routing.php** was added in v4.4.0. In previous versions, the setter methods were used in **app/Config/Routes.php** to change settings.

### Default Namespace

When matching a controller to a route, the router will add the default namespace value to the front of the controller specified by the route. By default, this value is `App\Controllers`.

If you set the value to an empty string (`''`), it leaves each route to specify the fully namespaced controller:

```php
<?php

// In app/Config/Routing.php
use CodeIgniter\Config\Routing as BaseRouting;

// ...
class Routing extends BaseRouting
{
    // ...
    public string $defaultNamespace = '';
    // ...
}

// In app/Config/Routes.php
// Controller is \Users
$routes->get('users', 'Users::index');

// Controller is \Admin\Users
$routes->get('users', 'Admin\Users::index');
```

If your controllers are not explicitly namespaced, there is no need to change this. If you namespace your controllers, then you can change this value to save typing:

```php
<?php

// This can be overridden in app/Config/Routes.php
$routes->setDefaultNamespace('App');

// Controller is \App\Users
$routes->get('users', 'Users::index');

// Controller is \App\Admin\Users
$routes->get('users', 'Admin\Users::index');
```

### Default Method

This setting is used when the route handler only has the controller name and no method name listed. The default value is `index`.

```
// In app/Config/Routing.php
public string $defaultMethod = 'index';
```

!!! note "Note"
    The `$defaultMethod` is also common with Auto Routing. See [Auto Routing (Improved)](auto_routing_improved.md#default-method_1) or [Auto Routing (Legacy)](#default-method-legacy).

If you define the following route:

```
$routes->get('/', 'Home');
```

the `index()` method of the `App\Controllers\Home` controller is executed when the route matches.

!!! note "Note"
    Method names beginning with `_` cannot be used as the default method. However, starting with v4.5.0, the `__invoke` method is allowed.

### Translate URI Dashes

This option enables you to automatically replace dashes (`-`) with underscores in the controller and method URI segments when used in Auto Routing, thus saving you additional route entries if you need to do that. This is required because the dash isn't a valid class or method name character and would cause a fatal error if you try to use it:

```php
<?php

// In app/Config/Routing.php
use CodeIgniter\Config\Routing as BaseRouting;

// ...
class Routing extends BaseRouting
{
    // ...
    public bool $translateURIDashes = true;
    // ...
}

// This can be overridden in app/Config/Routes.php
$routes->setTranslateURIDashes(true);
```

!!! note "Note"
    When using Auto Routing (Improved), prior to v4.4.0, if `$translateURIDashes` is true, two URIs correspond to a single controller method, one URI for dashes (e.g., **foo-bar**) and one URI for underscores (e.g., **foo_bar**). This was incorrect behavior. Since v4.4.0, the URI for underscores (**foo_bar**) is not accessible.

### Use Defined Routes Only

Since v4.2.0, auto-routing is disabled by default.

When no defined route is found that matches the URI, the system will attempt to match that URI against the controllers and methods when Auto Routing is enabled.

You can disable this automatic matching, and restrict routes to only those defined by you, by setting the `$autoRoute` property to false:

```php
<?php

// In app/Config/Routing.php
use CodeIgniter\Config\Routing as BaseRouting;

// ...
class Routing extends BaseRouting
{
    // ...
    public bool $autoRoute = false;
    // ...
}

// This can be overridden in app/Config/Routes.php
$routes->setAutoRoute(false);
```

!!! warning "Warning"
    If you use the [CSRF protection](../libraries/security.md), it does not protect **GET** requests. If the URI is accessible by the GET method, the CSRF protection will not work.

### 404 Override

When a page is not found that matches the current URI, the system will show a generic 404 view. Using the `$override404` property within the routing config file, you can define a controller class/method for 404 routes.

```php
<?php

// In app/Config/Routing.php
use CodeIgniter\Config\Routing as BaseRouting;

// ...
class Routing extends BaseRouting
{
    // ...
    public ?string $override404 = 'App\Errors::show404';
    // ...
}
```

You can also change what happens by specifying an action to happen with the `set404Override()` method in the Routes config file. The value can be either a valid class/method pair, or a Closure:

```php
<?php

// In app/Config/Routes.php
// Would execute the show404 method of the App\Errors class
$routes->set404Override('App\Errors::show404');

// Will display a custom view.
$routes->set404Override(static function () {
    // If you want to get the URI segments.
    $segments = request()->getUri()->getSegments();

    return view('my_errors/not_found.html');
});
```

!!! note "Note"
    Starting with v4.5.0, the 404 Override feature sets the Response status code to `404` by default. In previous versions, the code was `200`. If you want to change the status code in the controller, see `CodeIgniter\HTTP\Response::setStatusCode()` for information on how to set the status code.

### Route Processing by Priority

Enables or disables processing of the routes queue by priority. Lowering the priority is defined in the route option. Disabled by default. This functionality affects all routes. For an example use of lowering the priority, see [Route Priority](#route-priority):

```php
<?php

// In app/Config/Routing.php
use CodeIgniter\Config\Routing as BaseRouting;

// ...
class Routing extends BaseRouting
{
    // ...
    public bool $prioritize = true;
    // ...
}

// In app/Config/Routes.php
// to enable
$routes->setPrioritize();

// to disable
$routes->setPrioritize(false);
```

### Multiple URI Segments as One Parameter

!!! success "Available from version 4.5.0"

When this option is enabled, a placeholder that matches multiple segments, such as `(:any)`, will be passed directly as it is to one parameter, even if it contains multiple segments.

```php
<?php

// In app/Config/Routing.php
use CodeIgniter\Config\Routing as BaseRouting;

class Routing extends BaseRouting
{
    // ...
    public bool $multipleSegmentsOneParam = true;
    // ...
}
```

For example, the route:

```php
<?php

$routes->get('product/(:any)', 'Catalog::productLookup/$1');
```

will match **product/123**, **product/123/456**, **product/123/456/789** and so on. And if the URI is **product/123/456**, `123/456` will be passed to the first parameter of the `Catalog::productLookup()` method.

## Auto Routing (Improved)

!!! success "Available from version 4.2.0"

Auto Routing (Improved) is a new, more secure automatic routing system.

See [Auto Routing (Improved)](auto_routing_improved.md) for details.

## Auto Routing (Legacy)

!!! important "Important"
    This feature exists only for backward compatibility. Do not use it in new projects. Even if you are already using it, we recommend that you use the [Auto Routing (Improved)](auto_routing_improved.md#auto-routing-improved) instead.

Auto Routing (Legacy) is a routing system from CodeIgniter 3. It can automatically route HTTP requests based on conventions and execute the corresponding controller methods.

It is recommended to define all routes in the **app/Config/Routes.php** file, or to use [Auto Routing (Improved)](auto_routing_improved.md#auto-routing-improved).

!!! warning "Warning"
    To prevent misconfiguration and miscoding, we recommend that you do not use the Auto Routing (Legacy) feature. It is easy to create vulnerable apps where controller filters or CSRF protection are bypassed.

!!! important "Important"
    Auto Routing (Legacy) routes an HTTP request with **any** HTTP method to a controller method.

### Enable Auto Routing (Legacy)

Since v4.2.0, auto-routing is disabled by default.

To use it, you need to set the `$autoRoute` option to `true` in **app/Config/Routing.php**:

```
public bool $autoRoute = true;
```

And set the property `$autoRoutesImproved` to `false` in **app/Config/Feature.php**:

```
public bool $autoRoutesImproved = false;
```

### URI Segments (Legacy)

The segments in the URL, following the Model-View-Controller approach, usually represent:

```
example.com/class/method/ID
```

1.  The first segment represents the controller **class** that should be invoked.
2.  The second segment represents the class **method** that should be called.
3.  The third, and any additional segments, represent the ID and any variables that will be passed to the controller.

Consider this URI:

```
example.com/index.php/helloworld/index/1
```

In the above example, CodeIgniter would attempt to find a controller named **Helloworld.php** and execute the `index()` method, passing `'1'` as the first argument.

See [Auto Routing (Legacy) in Controllers](controllers.md#auto-routing-legacy) for more info.

### Configuration Options (Legacy)

These options are available in the **app/Config/Routing.php** file.

#### Default Controller (Legacy)

##### For Site Root URI (Legacy)

When a user visits the root of your site (i.e., **example.com**), the controller to use is determined by the value set to the `$defaultController` property, unless a route exists for it explicitly.

The default value for this is `Home`, which matches the controller at **app/Controllers/Home.php**:

```
public string $defaultController = 'Home';
```

##### For Directory URI (Legacy)

The default controller is also used when no matching route has been found, and the URI would point to a directory in the controllers directory. For example, if the user visits **example.com/admin**, and a controller was found at **app/Controllers/Admin/Home.php**, it would be used.

See [Auto Routing (Legacy) in Controllers](controllers.md#auto-routing-legacy) for more info.

#### Default Method (Legacy)

This works similarly to the default controller setting, but is used to determine the default method that is used when a controller is found that matches the URI, but no segment exists for the method. The default value is `index`.

In this example, if the user were to visit **example.com/products**, and a `Products` controller existed, the `Products::listAll()` method would be executed:

```
public string $defaultMethod = 'listAll';
```

## Confirming Routes

CodeIgniter has the following [command](../cli/spark_commands.md) to display all routes.

### spark routes

Displays all routes and filters:

```console
php spark routes
```

The output is like the following:

```none
+---------+---------+---------------+-------------------------------+----------------+---------------+
| Method  | Route   | Name          | Handler                       | Before Filters | After Filters |
+---------+---------+---------------+-------------------------------+----------------+---------------+
| GET     | /       | »             | \App\Controllers\Home::index  |                | toolbar       |
| GET     | feed    | »             | (Closure)                     |                | toolbar       |
+---------+---------+---------------+-------------------------------+----------------+---------------+
```

The *Method* column shows the HTTP method that the route is listening for.

The *Route* column shows the route path to match. The route of a defined route is expressed as a regular expression.

Since v4.3.0, the *Name* column shows the route name. `»` indicates the name is the same as the route path.

!!! important "Important"
    The system is not perfect. For routes containing regular expression patterns like `([^/]+)` or `{locale}`, the *Filters* displayed might not be correct (if you set a complicated URI pattern for the filters in **app/Config/Filters.php**), or they are displayed as `<unknown>`.

    The [spark filter:check](filters.md#filtercheck) command can be used to check for 100% accurate filters.

#### Auto Routing (Improved)

When you use Auto Routing (Improved), the output is like the following:

```none
+-----------+-------------------------+---------------+-----------------------------------+----------------+---------------+
| Method    | Route                   | Name          | Handler                           | Before Filters | After Filters |
+-----------+-------------------------+---------------+-----------------------------------+----------------+---------------+
| GET(auto) | product/list/../..[/..] |               | \App\Controllers\Product::getList |                | toolbar       |
+-----------+-------------------------+---------------+-----------------------------------+----------------+---------------+
```

The *Method* will be like `GET(auto)`.

`/..` in the *Route* column indicates one segment. `[/..]` indicates it is optional.

!!! note "Note"
    When auto-routing is enabled and you have the route `home`, it can also be accessed by `Home`, or maybe by `hOme`, `hoMe`, `HOME`, etc., but the command will show only `home`.

If you see a route starting with `x` like the following, it indicates an invalid route that won't be routed, but the controller has a public method for routing.

```none
+-----------+----------------+------+-------------------------------------+----------------+---------------+
| Method    | Route          | Name | Handler                             | Before Filters | After Filters |
+-----------+----------------+------+-------------------------------------+----------------+---------------+
| GET(auto) | x home/foo     |      | \App\Controllers\Home::getFoo       | <unknown>      | <unknown>     |
+-----------+----------------+------+-------------------------------------+----------------+---------------+
```

The above example shows that you have the `\App\Controllers\Home::getFoo()` method, but it is not routed because it is the default controller (`Home` by default) and the default controller name must be omitted in the URI. You should delete the `getFoo()` method.

!!! note "Note"
    Prior to v4.3.4, the invalid route was displayed as a normal route due to a bug.

#### Auto Routing (Legacy)

When you use Auto Routing (Legacy), the output is like the following:

```none
+--------+--------------------+---------------+-----------------------------------+----------------+---------------+
| Method | Route              | Name          | Handler                           | Before Filters | After Filters |
+--------+--------------------+---------------+-----------------------------------+----------------+---------------+
| auto   | product/list[/...] |               | \App\Controllers\Product::getList |                | toolbar       |
+--------+--------------------+---------------+-----------------------------------+----------------+---------------+
```

The *Method* will be `auto`.

`[/...]` in the *Route* column indicates any number of segments.

!!! note "Note"
    When auto-routing is enabled and you have the route `home`, it can also be accessed by `Home`, or maybe by `hOme`, `hoMe`, `HOME`, etc., but the command will show only `home`.

#### Sort by Handler

!!! success "Available from version 4.3.0"

You can sort the routes by *handler*:

```console
php spark routes --sort-by-handler
```

#### Specify Host

!!! success "Available from version 4.4.0"

You can specify the host in the request URL with the `--host` option:

```console
php spark routes --host accounts.example.com
```

## Getting Routing Information

In CodeIgniter 4, understanding and managing routing information is crucial for handling HTTP requests effectively. This involves retrieving details about the active controller and method, as well as the filters applied to a specific route. Below, we explore how to access this routing information to assist in tasks such as logging, debugging, or implementing conditional logic.

### Retrieving the Current Controller/Method Names

In some cases, you might need to determine which controller and method have been triggered by the current HTTP request. This can be useful for logging, debugging, or conditional logic based on the active controller method.

CodeIgniter 4 provides a simple way to access the current route's controller and method names using the `Router` class. Here is an example:

```php
<?php

// Get the router instance.
/** @var \CodeIgniter\Router\Router $router */
$router = service('router');

// Retrieve the fully qualified class name of the controller handling the current request.
$controller = $router->controllerName();

// Retrieve the method name being executed in the controller for the current request.
$method = $router->methodName();

echo 'Current Controller: ' . $controller . '<br>';
echo 'Current Method: ' . $method;
```

This functionality is particularly useful when you need to dynamically interact with your controller or log which method is handling a particular request.

### Getting Active Filters for the Current Route

[Filters](filters.md) are a powerful feature that enables you to perform operations such as authentication, logging, and security checks before or after processing HTTP requests. To access the active filters for a specific route, you can use the `CodeIgniter\Router\Router::getFilters()` method from the `Router` class.

This method returns a list of filters that are currently active for the route being processed:

```php
<?php

// Get the router instance.
/** @var \CodeIgniter\Router\Router $router */
$router  = service('router');
$filters = $router->getFilters();

echo 'Active Filters for the Route: ' . implode(', ', $filters);
```

!!! note "Note"
    The `getFilters()` method returns only the filters defined for the specific route. It does not include global filters or those specified in the **app/Config/Filters.php** file.

### Getting Matched Route Options for the Current Route

When we're defining routes, they may have optional parameters: `filter`, `namespace`, `hostname`, `subdomain`, `offset`, `priority`, `as`. All of them are described above. Additionally, if we use `addRedirect()` we can also expect the `redirect` key. To access the values of these parameters, we can call `Router::getMatchedRouteOptions()`. Here is an example of the returned array:

```php
<?php

// Get the router instance.
/** @var \CodeIgniter\Router\Router $router */
$router  = service('router');
$options = $router->getMatchedRouteOptions();

echo 'Route name: ' . $options['as'];

print_r($options);

// Route name: api:auth
//
// Array
// (
//     [filter] => api-auth
//     [namespace] => App\API\v1
//     [hostname] => example.com
//     [subdomain] => api
//     [offset] => 1
//     [priority] => 1
//     [as] => api:auth
// )
```
