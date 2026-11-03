# Services

## Introduction

### What are Services?

The **Services** in CodeIgniter 4 provide the functionality to create and share new class instances. They are implemented as the `Config\Services` class.

All of the core classes within CodeIgniter are provided as "services". This simply means that, instead of hard-coding a class name to load, the classes to call are defined within a very simple configuration file. This file acts as a type of factory to create new instances of the required class.

### Why use Services?

A quick example will probably make things clearer, so imagine that you need to pull in an instance of the Timer class. The simplest method would simply be to create a new instance of that class:

```php
<?php

$timer = new \CodeIgniter\Debug\Timer();
```

And this works great, until you decide that you want to use a different timer class in its place. Maybe this one has some advanced reporting that the default timer does not provide. In order to do this, you now have to locate all of the places in your application where you have used the timer class. Since you might have left them in place to keep a performance log of your application constantly running, this might be a time-consuming and error-prone way to handle this. That's where services come in handy.

Instead of creating the instance ourselves, we let a central class create an instance of the class for us. This class is kept very simple. It only contains a method for each class that we want to use as a service. The method typically returns a **shared instance** of that class, passing any dependencies it might have into it. Then, we would replace our timer creation code with code that calls this global function or Services class:

```php
<?php

$timer = service('timer');

// The code above is the same as the code below.
$timer = \Config\Services::timer();
```

When you need to change the implementation used, you can modify the services configuration file, and the change happens automatically throughout your application without you having to do anything. Now you just need to take advantage of any new functionality and you're good to go. Very simple and error-resistant.

!!! note "Note"
    It is recommended to only create services within controllers. Other files, like models and libraries, should have the dependencies either passed into the constructor or through a setter method.

## How to Get a Service

As many CodeIgniter classes are provided as services, you can get them like the following:

```php
<?php

$timer = service('timer');
```

The `$timer` is an instance of the Timer class, and if you call `service('timer')` again, you will get exactly the same instance.

The Services typically return a **shared instance** of the class. The following code creates a `CURLRequest` instance on the first call, and the second call returns exactly the same instance.

```php
<?php

$options1 = [
    'baseURI' => 'http://example.com/api/v1/',
    'timeout' => 3,
];
$client1 = service('curlrequest', $options1);

$options2 = [
    'baseURI' => 'http://another.example.com/api/v2/',
    'timeout' => 10,
];
$client2 = service('curlrequest', $options2);
// $options2 does not work.
// $client2 is the exactly same instance as $client1.
```

Therefore, the parameter `$options2` for the `$client2` does not work. It is just ignored.

### Getting a New Instance

If you want to get a new instance of the Timer class, you need to pass `false` to the argument `$getShared`:

```php
<?php

$timer = \Config\Services::timer(false);
```

### Convenience Functions

Two functions have been provided for getting a service. These functions are always available.

#### service()

The first is `service()`, which provides access to the requested service. The only required parameter is the service name. This is the same as the method name within the Services file. Without additional parameters, repeated calls return the same shared instance:

```php
<?php

$logger = service('logger');

// The code above is the same as the code below.
$logger = \Config\Services::logger();
```

!!! note "Note"
    Since v4.5.0, when you don't pass parameters to the service, the global function `service()` is recommended due to performance improvements.

When parameters are passed, `service()` calls the service method directly. Its `$getShared` argument and default determine whether the instance is shared.

If the creation method requires additional parameters, they can be passed after the service name:

```php
<?php

$renderer = service('renderer', APPPATH . 'views/');

// The code above is the same as the code below.
$renderer = \Config\Services::renderer(APPPATH . 'views/');
```

#### single_service()

The second function, `single_service()`, works just like `service()` but returns a new instance of the class:

```php
<?php

$logger = single_service('logger');

// The code above is the same as the code below.
$logger = \Config\Services::logger(false);
```

## Defining Services

To make services work well, you have to be able to rely on each class having a constant API, or [interface](https://www.php.net/manual/en/language.oop5.interfaces.php), to use. Almost all of CodeIgniter's classes provide an interface that they adhere to. When you want to extend or replace core classes, you only need to ensure you meet the requirements of the interface and you know that the classes are compatible.

For example, the `RouteCollection` class implements the `RouteCollectionInterface`. When you want to create a replacement that provides a different way to create routes, you just need to create a new class that implements the `RouteCollectionInterface`:

```php
<?php

namespace App\Router;

use CodeIgniter\Router\RouteCollectionInterface;

class MyRouteCollection implements RouteCollectionInterface
{
    // Implement required methods here.
}
```

Finally, add the `routes()` method to **app/Config/Services.php** to create a new instance of `MyRouteCollection` instead of `CodeIgniter\Router\RouteCollection`:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    // ...

    public static function routes()
    {
        return new \App\Router\MyRouteCollection(static::locator(), config('Modules'));
    }
}
```

### Allowing Parameters

In some instances, you will want the option to pass a setting to the class during instantiation. Since the services file is a very simple class, it is easy to make this work.

A good example is the `renderer` service. By default, we want this class to be able to find the views at `APPPATH . 'views/'`. We want the developer to have the option of changing that path, though, if their needs require it. So the class accepts the `$viewPath` as a constructor parameter. The service method looks like this:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    // ...

    public static function renderer($viewPath = APPPATH . 'views/')
    {
        return new \CodeIgniter\View\View($viewPath);
    }
}
```

This sets the default path in the constructor method, but allows for easily changing the path it uses:

```php
<?php

$renderer = \Config\Services::renderer('/shared/views/');
```

### Shared Classes

There are occasions where you need to require that only a single instance of a service is created. This is easily handled with the `getSharedInstance()` method that is called from within the factory method. This handles checking if an instance has been created and saved within the class, and, if not, creates a new one. All of the factory methods provide a `$getShared = true` value as the last parameter. You should stick to the method also:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    // ...

    public static function routes($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('routes');
        }

        return new \App\Router\MyRouteCollection(static::locator(), config('Modules'));
    }
}
```

## Service Discovery

CodeIgniter can automatically discover any **Config/Services.php** files you may have created within any defined namespaces. This allows simple use of any module Services files. In order for custom Services files to be discovered, they must meet these requirements:

- Its namespace must be defined in **app/Config/Autoload.php**
- Inside the namespace, the file must be found at **Config/Services.php**
- It must extend `CodeIgniter\Config\BaseService`

A small example should clarify this.

Imagine that you've created a new directory, **Blog**, in your project root directory. This will hold a **Blog module** with controllers, models, etc, and you'd like to make some of the classes available as a service. The first step is to create a new file: **Blog/Config/Services.php**. The skeleton of the file should be:

```php
<?php

namespace Blog\Config;

use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    public static function postManager()
    {
        // ...
    }
}
```

Now you can use this file as described above. When you want to grab the posts service from any controller, you would simply use the framework's `Config\Services` class to grab your service:

```php
<?php

$postManager = service('postManager');
```

!!! note "Note"
    If multiple Services files have the same method name, the first one found will be the instance returned.

### Resetting Services Cache

!!! success "Available from version 4.6.0"

When the Services class is first called fairly early in the framework initialization process, the Services classes discovered by auto-discovery are cached in a property, and the property will not be updated.

If modules are dynamically loaded later, and there are Services in the modules, the cache must be updated.

This can be done by running `Config\Services::resetServicesCache()`. This will clear the cache, and force service discovery to run again when needed.
