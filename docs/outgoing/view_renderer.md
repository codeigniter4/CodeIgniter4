# View Renderer

## Using the View Renderer

The `view()` function is a convenience function that grabs an instance of the `renderer` service, sets the data, and renders the view. While this is often exactly what you want, you may find times where you want to work with it more directly. In that case you can access the View service directly:

```php
$view = service('renderer');
```

Alternatively, if you are not using the `View` class as your default renderer, you can instantiate it directly:

```php
$view = new \CodeIgniter\View\View();
```

!!! important "Important"
    You should create services only within controllers. If you need access to the View class from a library, you should set that as a dependency in your library's constructor.

Then you can use any of the three standard methods that it provides: `render()`, `setVar()` and `setData()`.

### What It Does

The `View` class processes conventional HTML/PHP scripts stored in the application's view path, after extracting view parameters into PHP variables, accessible inside the scripts. This means that your view parameter names need to be legal PHP variable names.

The View class uses an associative array internally to accumulate view parameters until you call its `render()` method. This means that your parameter (or variable) names need to be unique, or a later variable setting will override an earlier one.

This also impacts escaping parameter values for different contexts inside your script. You will have to give each escaped value a unique parameter name.

No special meaning is attached to parameters whose value is an array. It is up to you to process the array appropriately in your PHP code.

### Setting View Parameters

The `setVar()` method sets a view parameter.

```php
$view->setVar('name', 'Joe', 'html');
```

The `setData()` method sets multiple view parameters at once.

```php
$view->setData(['name' => 'George', 'position' => 'Boss']);
```

### Method Chaining

The `setVar()` and `setData()` methods are chainable, allowing you to combine a number of different calls together in a chain:

```php
$view->setVar('one', $one)
    ->setVar('two', $two)
    ->render('myView');
```

### Escaping Data

When you pass data to the `setVar()` and `setData()` functions, you have the option to escape the data to protect against cross-site scripting attacks. As the last parameter in either method, you can pass the desired context to escape the data for. See below for context descriptions.

If you don't want the data to be escaped, you can pass `null` or `'raw'` as the final parameter to each function:

```php
$view->setVar('one', $one, 'raw');
```

If you choose not to escape data, or you are passing in an object instance, you can manually escape the data within the view with the `esc()` function. The first parameter is the string to escape. The second parameter is the context to escape the data for (see below):

```
<?= esc($object->getStat()) ?>
```

#### Escaping Contexts

By default, the `esc()` and, in turn, the `setVar()` and `setData()` functions assume that the data you want to escape is intended to be used within standard HTML. However, if the data is intended for use in JavaScript, CSS, or in an href attribute, you would need different escaping rules to be effective. You can pass in the name of the context as the second parameter. Valid contexts are `'html'`, `'js'`, `'css'`, `'url'`, and `'attr'`:

```
<a href="<?= esc($url, 'url') ?>" data-foo="<?= esc($bar, 'attr') ?>">Some Link</a>

<script>
    var siteName = '<?= esc($siteName, 'js') ?>';
</script>

<style>
    body {
        background-color: <?= esc($bgColor, 'css') ?>;
    }
</style>
```

### View Renderer Options

Several options can be passed to the `render()` or `renderString()` methods:

- `$options`
    - `cache` - the time in seconds to save a view's results; ignored for `renderString()`.
    - `cache_name` - the ID used to save/retrieve a cached view result; defaults to the `$viewPath`; ignored for `renderString()`.
    - `debug` - can be set to false to disable the addition of debug code for the [Debug Toolbar](../testing/debugging.md#the-debug-toolbar).
- `$saveData` - true if the view data parameters should be retained for subsequent calls.

!!! note "Note"
    `$saveData` as defined by the interface must be a boolean, but implementing classes (like `View` below) may extend this to include `null` values.

## Class Reference

## CodeIgniter\View

### View

#### render($view[, $options[, $saveData = false]])

- **Parameters**  
    - **$view** `string` File name of the view source
    - **$options** `array` Array of options, as key/value pairs
    - **$saveData** `boolean|null` If true, will save data for use with any other calls. If false, will clean the data after rendering the view. If null, uses the config setting.

- **Returns**: The rendered text for the chosen view

- **Return type**: `string`

Builds the output based upon a file name and any data that has already been set:

```php
echo $view->render('myview');
```

#### renderString($view[, $options[, $saveData = false]])

- **Parameters**  
    - **$view** `string` Contents of the view to render, executed as PHP code
    - **$options** `array` Array of options, as key/value pairs
    - **$saveData** `boolean|null` If true, will save data for use with any other calls. If false, will clean the data after rendering the view. If null, uses the config setting.

- **Returns**: The rendered text for the chosen view

- **Return type**: `string`

Builds the output based upon a view fragment and any data that has already been set:

```php
echo $view->renderString('<div>My Sharona</div>');
```

!!! warning "Warning"
    `renderString()` executes the given view contents as PHP code, just like a view file. Treat them as trusted code: never pass contents that less-trusted users can edit, even if they are stored in your database. Escaping or validating such contents does not make them safe to execute. Pass user-provided values as view data instead.

#### setData([$data[, $context = null]])

- **Parameters**  
    - **$data** `array` Array of view data strings, as key/value pairs
    - **$context** `string` The context to use for data escaping.

- **Returns**: The Renderer, for method chaining

- **Return type**: `CodeIgniter\View\RendererInterface`

Sets several pieces of view data at once:

```php
$view->setData(['name' => 'George', 'position' => 'Boss']);
```

Supported escape contexts: `html`, `css`, `js`, `url`, `attr` or `raw`. If `'raw'`, no escaping will happen.

Each call adds to the array of data that the object is accumulating, until the view is rendered.

#### setVar($name[, $value = null[, $context = null]])

- **Parameters**  
    - **$name** `string` Name of the view data variable
    - **$value** `mixed` The value of this view data
    - **$context** `string` The context to use for data escaping.

- **Returns**: The Renderer, for method chaining

- **Return type**: `CodeIgniter\View\RendererInterface`

Sets a single piece of view data:

```php
$view->setVar('name', 'Joe', 'html');
```

Supported escape contexts: `html`, `css`, `js`, `url`, `attr` or `raw`. If `'raw'`, no escaping will happen.

If you use a view data variable that you have previously used for this object, the new value will replace the existing one.
