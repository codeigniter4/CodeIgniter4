# Creating Core System Classes

Every time CodeIgniter runs there are several base classes that are initialized automatically as part of the core framework. It is possible, however, to swap any of the core system classes with your own version or even just extend the core versions.

**Most users will never have any need to do this, but the option to replace or extend them does exist for those who would like to significantly alter the CodeIgniter core.**

!!! important "Important"
    Messing with a core system class has a lot of implications, so make sure you know what you are doing before attempting it.

## System Class List

The following core system classes may be initialized during execution, depending on the application's configuration and usage:

- `CodeIgniter\Autoloader\Autoloader`
- `CodeIgniter\Autoloader\FileLocator`
- `CodeIgniter\Cache\CacheFactory`
- `CodeIgniter\Cache\Handlers\BaseHandler`
- `CodeIgniter\Cache\Handlers\FileHandler`
- `CodeIgniter\Cache\ResponseCache`
- `CodeIgniter\CodeIgniter`
- `CodeIgniter\Config\BaseService`
- `CodeIgniter\Config\DotEnv`
- `CodeIgniter\Config\Factories`
- `CodeIgniter\Config\Services`
- `CodeIgniter\Controller`
- `CodeIgniter\Cookie\Cookie`
- `CodeIgniter\Cookie\CookieStore`
- `CodeIgniter\Debug\Exceptions`
- `CodeIgniter\Debug\Timer`
- `CodeIgniter\Events\Events`
- `CodeIgniter\Filters\Filters`
- `CodeIgniter\HTTP\CLIRequest` (if launched from command line only)
- `CodeIgniter\HTTP\ContentSecurityPolicy`
- `CodeIgniter\HTTP\Header`
- `CodeIgniter\HTTP\IncomingRequest` (if launched over HTTP)
- `CodeIgniter\HTTP\Message`
- `CodeIgniter\HTTP\OutgoingRequest`
- `CodeIgniter\HTTP\Request`
- `CodeIgniter\HTTP\Response`
- `CodeIgniter\HTTP\SiteURI`
- `CodeIgniter\HTTP\SiteURIFactory`
- `CodeIgniter\HTTP\URI`
- `CodeIgniter\HTTP\UserAgent` (if launched over HTTP)
- `CodeIgniter\Log\Logger`
- `CodeIgniter\Log\Handlers\BaseHandler`
- `CodeIgniter\Log\Handlers\FileHandler`
- `CodeIgniter\Router\RouteCollection`
- `CodeIgniter\Router\Router`
- `CodeIgniter\Superglobals`
- `CodeIgniter\View\View`

## Replacing Core Classes

To use one of your own system classes instead of a default one, ensure:

1.  the [Autoloader](../concepts/autoloader.md) can find your class,
2.  your new class implements the appropriate interface,
3.  and the appropriate [Service](../concepts/services.md) is modified to load your class in place of the core class.

### Creating Your Class

For example, if you have a new `App\Libraries\RouteCollection` class that you would like to use in place of the core system class, you would create your class like this:

```php
<?php

namespace App\Libraries;

use CodeIgniter\Router\RouteCollectionInterface;

class RouteCollection implements RouteCollectionInterface
{
    // ...
}
```

### Adding the Service

Then you would add the `routes` service in **app/Config/Services.php** to load your class instead:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    public static function routes(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('routes');
        }

        return new \App\Libraries\RouteCollection(static::locator(), config(Modules::class), config(Routing::class));
    }

    // ...
}
```

## Extending Core Classes

If all you need is to add some functionality to an existing library - perhaps add a method or two - then it's overkill to recreate the entire library. In this case, it's better to simply extend the class. Extending the class is nearly identical to [Replacing Core Classes](#replacing-core-classes) with one exception:

- The class declaration must extend the parent class.

For example, to extend the native `RouteCollection` class, you would declare your class with:

```php
<?php

namespace App\Libraries;

use CodeIgniter\Router\RouteCollection as BaseRouteCollection;

class RouteCollection extends BaseRouteCollection
{
    // ...
}
```

If you need to use a constructor in your class, make sure you call the parent constructor:

```php
<?php

namespace App\Libraries;

use CodeIgniter\Autoloader\FileLocatorInterface;
use CodeIgniter\Router\RouteCollection as BaseRouteCollection;
use Config\Modules;
use Config\Routing;

class RouteCollection extends BaseRouteCollection
{
    public function __construct(FileLocatorInterface $locator, Modules $moduleConfig, Routing $routing)
    {
        parent::__construct($locator, $moduleConfig, $routing);

        // your code here
    }
}
```

**Tip:** Any functions in your class that are named identically to the methods in the parent class will be used instead of the native ones (this is known as "method overriding"). This allows you to substantially alter the CodeIgniter core.
