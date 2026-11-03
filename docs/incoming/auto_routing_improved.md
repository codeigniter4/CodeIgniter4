# Auto Routing (Improved)

!!! success "Available from version 4.2.0"

## What is Auto Routing (Improved)?

By default, all routes must be [defined](routing.md#setting-routing-rules) in the configuration file.

However, with **Auto Routing (Improved)**, you can define the controller name and its method name according to the convention and it will be automatically routed. In other words, there is no need to define routes manually.

If you enable Auto Routing (Improved), when no defined route is found that matches the URI, the system will attempt to match that URI against the controllers and methods.

!!! important "Important"
    For security reasons, if a controller is used in the defined routes, Auto Routing (Improved) does not route to the controller.

!!! note "Note"
    Auto Routing (Improved) is disabled by default. To use it, see [Enable Auto Routing (Improved)](#enable-auto-routing-improved).

## Differences from Auto Routing (Legacy)

[Auto Routing (Legacy)](routing.md#auto-routing-legacy) is a routing system from CodeIgniter 3. If you are not familiar with it, go to the next section.

If you know it well, these are some changes in **Auto Routing (Improved)**:

- A controller method needs an HTTP verb prefix like `getIndex()`, `postCreate()`.
    - Since developers always know the HTTP method, a request with an unexpected HTTP method will never execute the controller.
- The Default Controller (`Home` by default) and the Default Method (`index` by default) must be omitted in the URI.
    - It restricts one-to-one correspondence between controller methods and URIs.
    - For example, by default, you can access `/`, but `/home` and `/home/index` will be 404 Not Found.
- It checks the method parameter count.
    - If there are more parameters in the URI than the method parameters, it results in 404 Not Found.
- It does not support the `_remap()` method.
    - It restricts one-to-one correspondence between controller methods and URIs.
    - But it has the [Default Method Fallback](#default-method-fallback) feature instead.
- It cannot access controllers in Defined Routes.
    - It completely separates controllers accessible via **Auto Routing** from those accessible via **Defined Routes**.

## Enable Auto Routing (Improved)

To use it, you need to set the `$autoRoute` option to `true` in **app/Config/Routing.php**:

```
public bool $autoRoute = true;
```

And you need to change the property `$autoRoutesImproved` to `true` in **app/Config/Feature.php**:

```
public bool $autoRoutesImproved = true;
```

!!! important "Important"
    When you use Auto Routing (Improved), you must remove the line `$routes->get('/', 'Home::index');` in **app/Config/Routes.php**, because defined routes take precedence over Auto Routing, and controllers defined in the defined routes are denied access by Auto Routing (Improved) for security reasons.

## URI Segments

The segments in the URL, following the Model-View-Controller approach, usually represent:

```
http://example.com/{class}/{method}/{param1}
```

1.  The first segment represents the controller **class** that should be invoked.
2.  The second segment represents the class **method** that should be called.
3.  The third, and any additional segments, represent any **parameters** that will be passed to the controller method.

Consider this URI:

```
http://example.com/hello-world/hello/1
```

In the above example, when you send an HTTP request with the **GET** method, Auto Routing (Improved) would attempt to find a controller named `App\Controllers\HelloWorld` and execute the `getHello()` method, passing `'1'` as the first parameter.

!!! note "Note"
    A controller method that will be executed by Auto Routing (Improved) needs an HTTP verb (`get`, `post`, `put`, etc.) prefix like `getIndex()`, `postCreate()`.

!!! note "Note"
    When a controller's short name matches the first segment of a URI, it will be loaded.

## Let's try it: Hello World!

Let's create a simple controller so you can see it in action.

### Create a Controller

Using your text editor, create a file called **HelloWorld.php** in your **app/Controllers** directory, and put the following code in it.

```php
<?php

namespace App\Controllers;

class HelloWorld extends BaseController
{
    public function getIndex()
    {
        return 'Hello World!';
    }
}
```

!!! important "Important"
    The file must be called **HelloWorld.php**. When you use Auto Routing (Improved), controller class names MUST be CamelCase.

You will notice that the `HelloWorld` Controller is extending the `BaseController`. You can also extend the `CodeIgniter\Controller` if you do not need the functionality of the BaseController.

The BaseController provides a convenient place for loading components and performing functions that are needed by all your controllers. You can extend this class in any new controller.

!!! important "Important"
    A controller method that will be executed by Auto Routing (Improved) needs an HTTP verb (`get`, `post`, `put`, etc.) prefix like `getIndex()`, `postCreate()`.

### Check the Routes

You can check your routes with the `spark routes` command.

```console
php spark routes
```

If you did it right, you should see:

```none
+-----------+-------------+------+---------------------------------------+----------------+---------------+
| Method    | Route       | Name | Handler                               | Before Filters | After Filters |
+-----------+-------------+------+---------------------------------------+----------------+---------------+
| GET(auto) | hello-world |      | \App\Controllers\HelloWorld::getIndex |                |               |
+-----------+-------------+------+---------------------------------------+----------------+---------------+
```

See [spark routes](routing.md#spark-routes) for the output details.

### Visit Your Site

Now visit your site using a URL similar to this:

```
http://example.com/hello-world
```

The system automatically translates a URI with dashes (`-`) to CamelCase in the controller and method URI segments.

For example, the URI `sub-dir/hello-controller/some-method` will execute the `SubDir\HelloController::getSomeMethod()` method.

If you did it right, you should see:

```
Hello World!
```

## Examples of Controller Names

The following is a valid controller name, because `App\Controllers\HelloWorld` is CamelCase.

```php
<?php

namespace App\Controllers;

class HelloWorld extends BaseController
{
    // ...
}
```

The following is **not** valid, because the first letter (`h`) is not capitalized.

```php
<?php

namespace App\Controllers;

class helloworld extends BaseController
{
    // ...
}
```

The following is also **not** valid, because the first letter (`h`) is not capitalized.

```php
<?php

namespace App\Controllers;

class helloWorld extends BaseController
{
    // ...
}
```

## Controller Methods

### Method Visibility

When you define a method that is executable via HTTP request, the method must be declared as `public`.

!!! warning "Warning"
    For security reasons, be sure to declare any new utility methods as `protected` or `private`.

### Default Method

In the above example, the method name is `getIndex()`. The method (HTTP verb + `Index()`) is called the **Default Method**, and is loaded if the **second segment** of the URI is empty.

### Normal Methods

The second segment of the URI determines which method in the controller gets called.

Let's try it. Add a new method to your controller:

```php
<?php

namespace App\Controllers;

class HelloWorld extends BaseController
{
    public function getIndex()
    {
        return 'Hello World!';
    }

    public function getComment()
    {
        return 'I am not flat!';
    }
}
```

Now load the following URL to see the `getComment()` method:

```
http://example.com/hello-world/comment/
```

You should see your new message.

## Passing URI Segments to Your Methods

If your URI contains more than two segments, they will be passed to your method as parameters.

For example, let's say you have a URI like this:

```
http://example.com/products/shoes/sandals/123
```

Your method will be passed URI segments 3 and 4 (`'sandals'` and `'123'`):

```php
<?php

namespace App\Controllers;

class Products extends BaseController
{
    public function getShoes($type, $id)
    {
        return $type . $id;
    }
}
```

!!! note "Note"
    If there are more parameters in the URI than the method parameters, Auto Routing (Improved) does not execute the method, and it results in 404 Not Found.

## Default Controller

The **Default Controller** is a special controller that is used when a URI ends with a directory name or when a URI is not present, as will be the case when only your site root URL is requested.

By default, the Default Controller is `Home`.

!!! note "Note"
    Define only the default method (`getIndex()` for GET requests) in the default controller. If you define any other public method, that method will not be executed.

For more information, please refer to the [Configuration Options](#configuration-options).

## Default Method Fallback

!!! success "Available from version 4.4.0"

If the controller method corresponding to the URI segment of the method name does not exist, and if the default method is defined, the remaining URI segments are passed to the default method for execution.

```php
<?php

namespace App\Controllers;

class Product extends BaseController
{
    public function getIndex($id = null, $action = '')
    {
        // ...
    }
}
```

Load the following URL:

```
http://example.com/product/15/edit
```

The method will be passed URI segments 2 and 3 (`'15'` and `'edit'`).

!!! important "Important"
    If there are more parameters in the URI than the method parameters, Auto Routing (Improved) does not execute the method, and it results in 404 Not Found.

### Fallback to Default Controller

If the controller corresponding to the URI segment of the controller name does not exist, and if the default controller (`Home` by default) exists in the directory, the remaining URI segments are passed to the default controller's default method.

For example, when you have the following default controller `Home` in the **app/Controllers/News** directory:

```php
<?php

namespace App\Controllers\News;

use App\Controllers\BaseController;

class Home extends BaseController
{
    public function getIndex($id = null)
    {
        // ...
    }
}
```

Load the following URL:

```
http://example.com/news/101
```

The `News\Home` controller and the default `getIndex()` method will be found. So the default method will get the second URI segment (`'101'`).

!!! note "Note"
    If there is an `App\Controllers\News` controller, it takes precedence. The URI segments are searched sequentially and the first controller found is used.

!!! note "Note"
    If there are more parameters in the URI than the method parameters, Auto Routing (Improved) does not execute the method, and it results in 404 Not Found.

## Organizing Your Controllers into Sub-directories

If you are building a large application, you might want to hierarchically organize or structure your controllers into sub-directories. CodeIgniter permits you to do this.

Simply create sub-directories under the main **app/Controllers**, and place your controller classes within them.

!!! important "Important"
    Directory names MUST start with an uppercase letter and be CamelCase.

When using this feature, the first segment of your URI must specify the directory. For example, let's say you have a controller located here:

```
app/Controllers/Products/Shoes.php
```

To call the above controller, your URI will look something like this:

```
http://example.com/products/shoes/show/123
```

!!! note "Note"
    You cannot have directories with the same name in **app/Controllers** and **public**. This is because if there is a directory, the web server will search for it and it will not be routed to CodeIgniter.

Each of your sub-directories may contain a default controller which will be called if the URL contains *only* the sub-directory. Simply put a controller in there that matches the name of your default controller as specified in your **app/Config/Routing.php** file.

## Examples of Controller/Methods and URIs

In the case of a **GET** request with the default configuration, the mapping between controller/methods and URIs is as follows:

| Controller/Method | URI | Description |
|----|----|----|
| `Home::getIndex()` | / | The default controller and default method. |
| `Blog::getIndex()` | /blog | The default method. |
| `UserProfile::getIndex()` | /user-profile | The default method. |
| `Blog::getTags()` | /blog/tags |  |
| `Blog::getNews($id)` | /blog/news/123 |  |
| `Blog\Home::getIndex()` | /blog | Sub-directory `Blog` and the default controller and the default method. A `Blog` controller takes precedence. |
| `Blog\Tags::getIndex()` | /blog/tags | Sub-directory `Blog` and the default method. If a `Blog` controller exists, it takes precedence. |
| `Blog\News::getIndex($id)` | /blog/news/123 | Sub-directory `Blog` and the default method fallback. If a `Blog` controller exists, it takes precedence. |

## Applying Filters

Applying controller filters allows you to add processing before and after the controller method execution. This is especially handy during authentication or API logging.

If you use Auto Routing, set the filters to be applied in **app/Config/Filters.php**. See [Controller Filters](filters.md) for more information on setting up filters.

## Configuration Options

These options are available in the **app/Config/Routing.php** file.

### Default Controller

#### For Site Root URI

When a user visits the root of your site (i.e., **http://example.com**), the controller to use is determined by the value set to the `$defaultController` property, unless a route exists for it explicitly.

The default value for this is `Home`, which matches the controller at **app/Controllers/Home.php**:

```
public string $defaultController = 'Home';
```

#### For Directory URI

The default controller is also used when no matching route has been found, and the URI would point to a directory in the controllers directory. For example, if the user visits **http://example.com/admin**, and a controller was found at **app/Controllers/Admin/Home.php**, it would be used.

!!! important "Important"
    You cannot access the default controller with the URI of the controller name. When the default controller is `Home`, you can access **http://example.com/**, but if you access **http://example.com/home**, it will not be found.

### Default Method

This works similarly to the default controller setting, but is used to determine the default method that is used when a controller is found that matches the URI, but no segment exists for the method. The default value is `index`.

In this example, if the user were to visit **example.com/products**, and a `Products` controller existed, the `Products::getListAll()` method would be executed:

```
public string $defaultMethod = 'listAll';
```

!!! important "Important"
    You cannot access the controller with the URI of the default method name. In the example above, you can access **example.com/products**, but if you access **example.com/products/listall**, it will not be found.

### Translate URI To CamelCase

!!! success "Available from version 4.5.0"

!!! note "Note"
    Since v4.6.0, the `$translateUriToCamelCase` option is enabled by default.

Since v4.5.0, the `$translateUriToCamelCase` option has been implemented, which works well with CodeIgniter's current coding standards.

This option enables you to automatically translate a URI with dashes (`-`) to CamelCase in the controller and method URI segments.

For example, the URI `sub-dir/hello-controller/some-method` will execute the `SubDir\HelloController::getSomeMethod()` method.

!!! note "Note"
    When this option is enabled, the `$translateURIDashes` option is ignored.

#### Disable Translate URI To CamelCase

!!! note "Note"
    The option to disable "Translate URI To CamelCase" exists only for backward compatibility. We don't recommend disabling it.

To disable it, you need to set the `$translateUriToCamelCase` option to `false` in **app/Config/Routing.php**:

```
public bool $translateUriToCamelCase = false;
```

## Module Routing

!!! success "Available from version 4.4.0"

You can use auto routing even if you use [Code Modules](../general/modules.md) and place the controllers in a different namespace.

To route to a module, the `$moduleRoutes` property in **app/Config/Routing.php** must be set:

```
public array $moduleRoutes = [
    'blog' => 'Acme\Blog\Controllers',
];
```

The key is the first URI segment for the module, and the value is the controller namespace. In the above configuration, **http://localhost:8080/blog/foo/bar** will be routed to `Acme\Blog\Controllers\Foo::getBar()`.

!!! note "Note"
    If you define `$moduleRoutes`, the routing for the module takes precedence. In the above example, even if you have the `App\Controllers\Blog` controller, **http://localhost:8080/blog** will be routed to the default controller `Acme\Blog\Controllers\Home`.
