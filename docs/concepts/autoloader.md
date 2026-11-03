# Autoloading Files

Every application consists of a large number of classes in many different locations. The framework provides classes for core functionality. Your application will have a number of libraries, models, and other entities to make it work. You might have third-party classes that your project is using. Keeping track of where every single file is, and hard-coding that location into your files in a series of `require` statements is a massive headache and very error-prone. That's where autoloaders come in.

## CodeIgniter4 Autoloader

CodeIgniter provides a very flexible autoloader that can be used with very little configuration. It can locate individual namespaced classes that adhere to [PSR-4](https://www.php-fig.org/psr/psr-4/) autoloading directory structures.

The autoloader works great by itself, but can also work with other autoloaders, like [Composer](https://getcomposer.org), or even your own custom autoloaders, if needed. Because they're all registered through [spl_autoload_register](https://www.php.net/manual/en/function.spl-autoload-register.php), they work in sequence and don't get in each other's way.

The autoloader is always active, being registered with `spl_autoload_register()` at the beginning of the framework's execution.

!!! important "Important"
    You should always be careful about the case of filenames. Many developers develop on case-insensitive file systems on Windows or macOS. However, most server environments use case-sensitive file systems. If the file name case is incorrect, the autoloader cannot find the file on the server.

## Configuration

Initial configuration is done in **app/Config/Autoload.php**. This file contains two primary arrays: one for the classmap, and one for PSR-4 compatible namespaces.

### Namespaces

The recommended method for organizing your classes is to create one or more namespaces for your application's files.

The `$psr4` array in the configuration file allows you to map the namespace to the directory those classes can be found in:

```php
<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

class Autoload extends AutoloadConfig
{
    // ...
    public $psr4 = [
        APP_NAMESPACE => APPPATH,
    ];

    // ...
}
```

The key of each row is the namespace itself. This does not need a trailing backslash. The value is the location of the directory the classes can be found in.

By default, the namespace `App` is located in the **app** directory, and the namespace `Config` is located in the **app/Config** directory.

If you create class files in the locations and according to [PSR-4](https://www.php-fig.org/psr/psr-4/), the autoloader will autoload them.

### Confirming Namespaces

You can check the namespace configuration with the `spark namespaces` command:

```console
php spark namespaces
```

### Application Namespace

By default, the application directory is namespaced to the `App` namespace. You must namespace the controllers, libraries, or models in the application directory, and they will be found under the `App` namespace.

#### Config Namespace

Config files are namespaced in the `Config` namespace, not in `App\Config` as you might expect. This allows the core system files to always be able to locate them, even when the application namespace has changed.

!!! note "Note"
    Since v4.5.3 appstarter, the `Config\\` namespace has been added to **composer.json**'s `autoload.psr-4`.

#### Changing App Namespace

You may change this namespace by editing the **app/Config/Constants.php** file and setting the new namespace value under the `APP_NAMESPACE` setting:

```php
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');
```

And if you use the Composer autoloader, you also need to change the `App` namespace in your **composer.json**, and run `composer dump-autoload`.

```text
{
    ...
    "autoload": {
        "psr-4": {
            "App\\": "app/"    <-- Change
        },
        ...
    },
    ...
}
```

!!! note "Note"
    Since v4.5.0 appstarter, the `App\\` namespace has been added to **composer.json**'s `autoload.psr-4`. If your **composer.json** does not have it, adding it may improve your app's autoloading performance.

You will need to modify any existing files that are referencing the current namespace.

### Classmap

If you use third-party libraries that are not Composer packages and are not namespaced, you can load those classes using the classmap:

```php
<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

class Autoload extends AutoloadConfig
{
    // ...
    public $classmap = [
        'Markdown' => APPPATH . 'ThirdParty/markdown.php',
    ];

    // ...
}
```

The key of each row is the name of the class that you want to locate. The value is the path to locate it at.

## Composer Support

Composer support is automatically initialized by default.

By default, it looks for Composer's autoload file at `ROOTPATH . 'vendor/autoload.php'`. If you need to change the location of that file for any reason, you can modify the value defined in **app/Config/Constants.php**.

### Priority of Autoloaders

If the same namespace is defined in both CodeIgniter and Composer, Composer's autoloader will be the first one to get a chance to locate the file.

!!! note "Note"
    Prior to v4.5.0, if the same namespace was defined in both CodeIgniter and Composer, CodeIgniter's autoloader was the first one to get a chance to locate the file.

## FileLocator Caching

!!! success "Available from version 4.5.0"

**FileLocator** is responsible for finding files or getting a classname from a file, which cannot be achieved with PHP autoloading.

To improve its performance, FileLocator Caching has been implemented.

### How It Works

- Save all data found by FileLocator into a cache file when destructing, if the cache data is updated.
- Restore cached data when instantiating if cached data is available.

The cached data are used permanently.

### How to Delete Cached Data

Once stored, the cached data never expire.

So if you add or remove files or change existing file paths, or namespaces, old cached data will be returned and your app may not work properly.

In that case, you must manually delete the cache file. If you add a CodeIgniter package via Composer, you also need to delete the cache file.

You can use the `spark cache:clear` command:

```console
php spark cache:clear
```

Or simply delete the **writable/cache/FileLocatorCache** file.

!!! note "Note"
    The `spark optimize` command clears the cache.

### How to Enable FileLocator Caching

Set the following property to `true` in **app/Config/Optimize.php**:

```
public bool $locatorCacheEnabled = true;
```

Or you can enable it with the `spark optimize` command.

!!! note "Note"
    This property cannot be overridden by [environment variables](../general/configuration.md#configuration-classes-and-environment-variables).

!!! warning "Warning"
    Do not use this option when running the app in the [Worker Mode](../installation/worker_mode.md).
