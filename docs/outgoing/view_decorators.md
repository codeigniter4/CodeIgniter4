# View Decorators

## What are View Decorators?

View Decorators allow your application to modify the HTML output during the rendering process. This happens just before the output is cached, and allows you to apply custom functionality to your views.

You can use View Decorators with [View Renderer](view_renderer.md) or [View Parser](view_parser.md).

## Creating Decorators

### Creating Decorator Class

Creating your own view decorators requires creating a new class that implements `CodeIgniter\View\ViewDecoratorInterface`. This requires a single method that takes the generated HTML string, performs any modifications on it, and returns the resulting HTML.

```php
<?php

namespace App\Views\Decorators;

use CodeIgniter\View\ViewDecoratorInterface;

class MyDecorator implements ViewDecoratorInterface
{
    public static function decorate(string $html): string
    {
        // Modify the output here

        return $html;
    }
}
```

### Registering Decorator Class

Once created, the class must be registered in **app/Config/View.php**:

```php
<?php

namespace Config;

use CodeIgniter\Config\View as BaseView;

class View extends BaseView
{
    // ...

    public array $decorators = [
        'App\Views\Decorators\MyDecorator',
    ];
}
```

Now that it's registered, the decorator will be called for every view that is rendered or parsed. Decorators are called in the order specified in this configuration setting.
