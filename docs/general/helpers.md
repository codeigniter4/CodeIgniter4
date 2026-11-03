# Helper Functions

## What are Helpers?

Helpers, as the name suggests, help you with tasks. Each helper file is simply a collection of functions in a particular category. There are [URL Helpers](../helpers/url_helper.md) that assist in creating links, there are [Form Helpers](../helpers/form_helper.md) that help you create form elements, [Text Helpers](../helpers/text_helper.md) perform various text formatting routines, [Cookie Helpers](../helpers/cookie_helper.md) set and read cookies, [Filesystem Helpers](../helpers/filesystem_helper.md) help you deal with files, etc.

Unlike most other systems in CodeIgniter, Helpers are not written in an Object Oriented format. They are simple, procedural functions. Each helper function performs one specific task, with no dependence on other functions.

CodeIgniter does not load Helper Files by default, so the first step in using a Helper is to load it. Once loaded, it becomes globally available in your [controller](../incoming/controllers.md) and [views](../outgoing/views.md).

Helpers are typically stored in your **system/Helpers**, or **app/Helpers** directory.

## Loading Helpers

!!! note "Note"
    The [URL Helper](../helpers/url_helper.md) is always loaded so you do not need to load it yourself.

### Loading a Helper

Loading a helper file is quite simple using the following method:

```php
<?php

helper('name');
```

The above code loads the **name_helper.php** file.

!!! important "Important"
    CodeIgniter helper file names are all lowercase. Therefore, `helper('Name')` will not work on case-sensitive file systems such as Linux.

For example, to load the [Cookie Helper](../helpers/cookie_helper.md) file, which is named **cookie_helper.php**, you would do this:

```php
<?php

helper('cookie');
```

!!! note "Note"
    The `helper()` function does not return a value, so don't try to assign it to a variable. Just use it as shown.

#### Auto-Discovery and Composer Packages

By default, CodeIgniter will search for the helper files in all defined namespaces by [Auto-Discovery](modules.md#auto-discovery). You can check your defined namespaces with the spark command. See [Confirming Namespaces](../concepts/autoloader.md#confirming-namespaces).

If you use many Composer packages, you will have many defined namespaces. CodeIgniter will scan all namespaces by default.

To avoid wasting time scanning for irrelevant Composer packages, you can manually specify packages for Auto-Discovery. See [Specify Composer Packages](modules.md#specify-composer-packages) for details.

Or you can [specify a namespace](#loading-from-specified-namespace) for a helper that you want to load.

#### Load Order

The `helper()` function will scan through all defined namespaces and load in ALL matching helpers of the same name. This allows any module's helpers to be loaded, as well as any helpers you've created specifically for this application.

The load order is as follows:

1.  **app/Helpers** - Files loaded here are always loaded first.
2.  {namespace}/Helpers - All namespaces are looped through in the order they are defined.
3.  **system/Helpers** - The base file is loaded last.

### Loading Multiple Helpers

If you need to load more than one helper at a time, you can pass an array of file names in, and all of them will be loaded:

```php
<?php

helper(['cookie', 'date']);
```

### Loading in a Controller

A helper can be loaded anywhere within your controller methods (or even within your View files, although that's not a good practice), as long as you load it before you use it.

You can load your helpers in your controller constructor so that they become available automatically in any method, or you can load a helper in a specific method that needs it.

However, if you want to load one in your controller constructor, you can use the `$helpers` property in Controller instead. See [Controllers](../incoming/controllers.md#helpers).

### Loading from Specified Namespace

By default, CodeIgniter will search for the helper files in all defined namespaces and load all found files.

If you want to load only a helper in a specific namespace, prefix the name of the helper with the namespace that it can be located in. Within that namespaced directory, the loader expects it to live within a sub-directory named **Helpers**. An example will help you understand this.

For this example, assume that we have grouped together all of our Blog-related code into its own namespace, `Example\Blog`. The files exist on our server at **Modules/Blog/**. So, we would put our Helper files for the blog module in **Modules/Blog/Helpers/**. A **blog_helper** file would be at **Modules/Blog/Helpers/blog_helper.php**. Within our controller, we could use the following command to load the helper for us:

```php
<?php

helper('Example\Blog\blog');
```

You can also use the following way:

```php
<?php

helper('Example\Blog\Helpers\blog');
```

!!! note "Note"
    The functions within files loaded this way are not truly namespaced. The namespace is simply used as a convenient way to locate the files.

### Auto-loading Helpers

!!! success "Available from version 4.3.0"

If you find that you need a particular helper globally throughout your application, you can tell CodeIgniter to auto-load it during system initialization. This is done by opening the **app/Config/Autoload.php** file and adding the helper to the `$helpers` property.

## Using a Helper

Once you've loaded the Helper File containing the function you intend to use, you'll call it the way you would a standard PHP function.

For example, to create a link using the `anchor()` function in one of your view files, you would do this:

```php
<div>
<?= anchor('blog/comments', 'Click Here') ?>
</div>
```

Where `Click Here` is the name of the link, and `blog/comments` is the URI to the controller/method you wish to link to.

## Creating Helpers

### Creating Custom Helpers

The helper filename is the **helper name** and **\_helper.php**.

For example, to create an info helper, you’ll create a file named **app/Helpers/info_helper.php**, and add a function to the file:

```php
<?php

// app/Helpers/info_helper.php
use CodeIgniter\CodeIgniter;

/**
 * Returns CodeIgniter's version.
 */
function ci_version(): string
{
    return CodeIgniter::CI_VERSION;
}
```

You can add as many functions as you like to a single helper file.

### "Extending" Helpers

To "extend" Helpers, create a file in your **app/Helpers** folder with an identical name to the existing Helper.

If all you need to do is add some functionality to an existing helper (perhaps add a function or two, or change how a particular helper function operates), then it's overkill to replace the entire helper with your version. In this case, it's better to simply "extend" the Helper.

!!! note "Note"
    The term "extend" is used loosely since Helper functions are procedural and discrete and cannot be extended in the traditional programmatic sense. Under the hood, this gives you the ability to add to, or replace, the functions a Helper provides.

For example, to extend the native **Array Helper** you'll create a file named **app/Helpers/array_helper.php**, and add or override functions:

```php
<?php

// any_in_array() is not in the Array Helper, so it defines a new function
function any_in_array($needle, $haystack)
{
    $needle = is_array($needle) ? $needle : [$needle];

    foreach ($needle as $item) {
        if (in_array($item, $haystack, true)) {
            return true;
        }
    }

    return false;
}

// random_element() is included in Array Helper, so it overrides the native function
function random_element($array)
{
    shuffle($array);

    return array_pop($array);
}
```

!!! important "Important"
    Do not specify the namespace `App\Helpers`.

See [Load Order](#load-order) for the order in which helper files are loaded.

## Now What?

In the Table of Contents, you'll find a list of all the available [Helpers](../helpers/index.md). Browse each one to see what it does.
