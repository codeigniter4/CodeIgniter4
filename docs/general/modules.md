# Code Modules

CodeIgniter supports a form of code modularization to help you create reusable code. Modules are typically centered around a specific subject, and can be thought of as mini-applications within your larger application.

Any of the standard file types within the framework are supported, like controllers, models, views, config files, helpers, language files, etc. Modules may contain as few, or as many, of these as you like.

If you want to create a module as a Composer package, see also [Creating Composer Packages](../extending/composer_packages.md).

## Namespaces

The core element of the modules functionality comes from the [PSR-4 compatible autoloading](../concepts/autoloader.md) that CodeIgniter uses. While any code can use the PSR-4 autoloader and namespaces, the primary way to take full advantage of modules is to namespace your code and add it to **app/Config/Autoload.php**, in the `$psr4` property.

For example, let's say we want to keep a simple blog module that we can re-use between applications. We might create a folder with our company name, Acme, to store all of our modules within. We will put it right alongside our **app** directory in the main project root:

```
acme/        // New modules directory
app/
public/
system/
tests/
writable/
```

Open **app/Config/Autoload.php** and add the `Acme\Blog` namespace to the `$psr4` array property:

```php
<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

class Autoload extends AutoloadConfig
{
    // ...
    public $psr4 = [
        APP_NAMESPACE => APPPATH,
        'Acme\Blog'   => ROOTPATH . 'acme/Blog',
    ];

    // ...
}
```

Now that this is set up, we can access any file within the **acme/Blog** folder through the `Acme\Blog` namespace. This alone takes care of 80% of what is needed for modules to work, so you should be sure to familiarize yourself with namespaces and become comfortable with their use. Several file types will be scanned for automatically through all defined namespaces, a crucial ingredient for working with modules.

A common directory structure within a module will mimic the main application folder:

```
acme/
    Blog/
        Config/
        Controllers/
        Database/
            Migrations/
            Seeds/
        Helpers/
        Language/
            en/
        Libraries/
        Models/
        Views/
```

Of course, there is nothing forcing you to use this exact structure, and you should organize it in the manner that best suits your module, leaving out directories you don't need, creating new directories for Entities, Interfaces, or Repositories, etc.

## Autoloading Non-class Files

More often than not, your module will not contain only PHP classes but also others like procedural functions, bootstrapping files, module constants files, etc., which are not normally loaded the way classes are loaded. One approach for this is to `require` the file(s) at the start of the file where they would be used.

Another approach provided by CodeIgniter is to autoload these *non-class* files the way you would autoload your classes. All we need to do is provide the list of paths to those files and include them in the `$files` property of your **app/Config/Autoload.php** file.

```php
<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

class Autoload extends AutoloadConfig
{
    // ...

    public $files = [
        'path/to/my/functions.php',
        'path/to/my/constants.php',
        'path/to/my/bootstrap.php',
    ];

    // ...
}
```

## Auto-Discovery

Many times, you will need to specify the full namespace to files you want to include, but CodeIgniter can be configured to make integrating modules into your applications simpler by automatically discovering many different file types, including:

- [Events](../extending/events.md)
- [Filters](../incoming/filters.md)
- [Registrars](configuration.md#registrars)
- [Route files](../incoming/routing.md)
- [Services](../concepts/services.md)

This is configured in the file **app/Config/Modules.php**.

The auto-discovery system works by scanning for particular directories and files within PSR-4 namespaces that have been defined in **Config/Autoload.php** and Composer packages.

The discovery process would look for discoverable items on that path and should, for example, find the routes file at **acme/Blog/Config/Routes.php**.

### Enable/Disable Discovery

You can turn on or off all auto-discovery in the system with the `$enabled` class variable. False will disable all discovery, optimizing performance, but negating the special capabilities of your modules and Composer packages.

### Specify Discovery Items

With the `$aliases` option, you can specify which items are automatically discovered. If the item is not present, then no auto-discovery will happen for that item, but the others in the array will still be discovered.

### Discovery and Composer

Packages installed via Composer using PSR-4 namespaces will also be discovered by default. PSR-0 namespaced packages will not be detected.

#### Specify Composer Packages

!!! success "Available from version 4.3.0"

To avoid wasting time scanning for irrelevant Composer packages, you can manually specify packages to discover by editing the `$composerPackages` variable in **app/Config/Modules.php**:

```php
<?php

namespace Config;

use CodeIgniter\Modules\Modules as BaseModules;

class Modules extends BaseModules
{
    // ...

    public $composerPackages = [
        'only' => [
            // List up all packages to auto-discover
            'codeigniter4/shield',
        ],
    ];

    // ...
}
```

Alternatively, you can specify which packages to exclude from discovery.

```php
<?php

namespace Config;

use CodeIgniter\Modules\Modules as BaseModules;

class Modules extends BaseModules
{
    // ...

    public $composerPackages = [
        'exclude' => [
            // List up packages to exclude.
            'pestphp/pest',
        ],
    ];

    // ...
}
```

#### Disable Composer Package Discovery

If you do not want all of Composer's known directories to be scanned when locating files, you can turn this off by editing the `$discoverInComposer` variable in **app/Config/Modules.php**:

```php
<?php

namespace Config;

use CodeIgniter\Modules\Modules as BaseModules;

class Modules extends BaseModules
{
    public $discoverInComposer = false;

    // ...
}
```

## Working with Files

This section will take a look at each of the file types (controllers, views, language files, etc.) and how they can be used within the module. Some of this information is described in more detail in the relevant location of the user guide, but is being reproduced here so that it's easier to grasp how all of the pieces fit together.

### Routes

By default, [routes](../incoming/routing.md) are automatically scanned for within modules. This can be turned off in the **Modules** config file, described above.

!!! note "Note"
    Since the files are being included into the current scope, the `$routes` instance is already defined for you. It will cause errors if you attempt to redefine that class.

When working with modules, it can be a problem if the routes in the application contain wildcards. In that case, see [Route Priority](../incoming/routing.md#route-priority).

### Filters

!!! danger "Deprecated since version 4.4.2"

!!! note "Note"
    This feature is deprecated. Use [Registrars](configuration.md#registrars) instead, like the following:

    ```php
    <?php

    namespace CodeIgniter\Shield\Config;

    use CodeIgniter\Shield\Filters\SessionAuth;
    use CodeIgniter\Shield\Filters\TokenAuth;

    class Registrar
    {
        /**
         * Registers the Shield filters.
         */
        public static function Filters(): array
        {
            return [
                'aliases' => [
                    'session' => SessionAuth::class,
                    'tokens'  => TokenAuth::class,
                ],
            ];
        }
    }
    ```

By default, [filters](../incoming/filters.md) are automatically scanned for within modules. This can be turned off in the **Modules** config file, described above.

!!! note "Note"
    Since the files are being included into the current scope, the `$filters` instance is already defined for you. It will cause errors if you attempt to redefine that class.

In the module's **Config/Filters.php** file, you need to define the aliases of the filters you use:

```php
<?php

$filters->aliases['menus'] = \App\Filters\MenusFilter::class;
```

### Controllers

Controllers outside of the main **app/Controllers** directory cannot be automatically routed by URI detection, but must be specified within the Routes file itself:

```php
<?php

// Routes.php
$routes->get('blog', '\Acme\Blog\Controllers\Blog::index');
```

To reduce the amount of typing needed here, the **group** routing feature is helpful:

```php
<?php

$routes->group('blog', ['namespace' => 'Acme\Blog\Controllers'], static function ($routes) {
    $routes->get('/', 'Blog::index');
});
```

### Config Files

No special change is needed when working with configuration files. These are still namespaced classes and loaded with the `new` keyword:

```php
<?php

$config = new \Acme\Blog\Config\Blog();
```

Config files are automatically discovered when you use the always-available `config()` function and pass a short classname to it.

!!! note "Note"
    We don't recommend you use the same short classname in modules. Modules that need to override or add to known configurations in **app/Config/** should use [Implicit Registrars](configuration.md#registrars).

!!! note "Note"
    Prior to v4.4.0, `config()` found the file in **app/Config/** when there was a class with the same shortname, even if you specified a fully qualified class name like `config(\Acme\Blog\Config\Blog::class)`. This behavior was fixed in v4.4.0, and it now returns the specified instance.

### Migrations

Migration files will be automatically discovered within defined namespaces. All migrations found across all namespaces will be run every time.

### Seeds

Seed files can be both used from the CLI and called from within other seed files as long as the full namespace is provided. If calling on the CLI, you will need to provide double backslashes:

For Unix:

```console
php spark db:seed Acme\\Blog\\Database\\Seeds\\TestPostSeeder
```

For Windows:

```console
php spark db:seed Acme\Blog\Database\Seeds\TestPostSeeder
```

### Helpers

Helpers will be automatically discovered within defined namespaces when using the `helper()` function, as long as it is within the namespace's **Helpers** directory:

```php
<?php

helper('blog');
```

You can specify namespaces. See [Loading from Specified Namespace](helpers.md#loading-from-specified-namespace) for details.

### Language Files

Language files are located automatically from defined namespaces when using the `lang()` function, as long as the file follows the same directory structure as the main application directory.

### Libraries

Libraries are always instantiated by their fully-qualified class name, so no special access is provided:

```php
<?php

$lib = new \Acme\Blog\Libraries\BlogLib();
```

### Models

If you instantiate models with the `new` keyword by their fully-qualified class names, no special access is provided:

```php
<?php

$model = new \Acme\Blog\Models\PostModel();
```

Model files are automatically discovered when you use the always-available `model()` function.

!!! note "Note"
    We don't recommend you use the same short classname in modules.

!!! note "Note"
    Prior to v4.4.0, `model()` found the file in **app/Models/** when there was a class with the same shortname, even if you specified a fully qualified class name like `model(\Acme\Blog\Model\PostModel::class)`. See the Note in [Passing Fully Qualified Classname](../concepts/factories.md#passing-fully-qualified-classname) for more information.

### Views

Views can be loaded using the class namespace as described in the [views](../outgoing/views.md#namespaced-views) documentation:

```php
<?php

echo view('Acme\Blog\Views\index');
```
