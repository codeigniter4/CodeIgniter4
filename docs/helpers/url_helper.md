# URL Helper

The URL Helper file contains functions that assist in working with URLs.

## Loading this Helper

This helper is automatically loaded by the framework on every request.

## Available Functions

The following functions are available:

#### site_url([$uri = ''[, $protocol = null[, $altConfig = null]]])

- **Parameters**  
    - **$uri** `array|string` URI string or array of URI segments.
    - **$protocol** `string` Protocol, e.g., `'http'` or `'https'`. If an empty string `''` is set, a protocol-relative link is returned.
    - \Config\App `$altConfig` Alternate configuration to use.

- **Returns**: Site URL

- **Return type**: `string`

!!! note "Note"
    Since v4.3.0, if you set `Config\App::$allowedHostnames`, this returns the URL with the hostname set in it if the current URL matches.

Returns your site URL, as specified in your config file. The **index.php** file (or whatever you have set as your site `Config\App::$indexPage` in your config file) will be added to the URL, as will any URI segments you pass to the function.

You are encouraged to use this function any time you need to generate a local URL so that your pages become more portable in the event your URL changes.

Segments can be optionally passed to the function as a string or an array. Here is a string example:

```php
<?php

echo site_url('news/local/123');
```

The above example would return something like: **http://example.com/index.php/news/local/123**

Here is an example of segments passed as an array:

```php
<?php

$segments = ['news', 'local', '123'];
echo site_url($segments);
```

You may find the alternate configuration useful if generating URLs for a different site than yours, which contains different configuration preferences. We use this for unit testing the framework itself.

#### base_url([$uri = ''[, $protocol = null]])

- **Parameters**  
    - **$uri** `array|string` URI string or array of URI segments.
    - **$protocol** `string` Protocol, e.g., `'http'` or `'https'`. If an empty string `''` is set, a protocol-relative link is returned.

- **Returns**: Base URL

- **Return type**: `string`

!!! note "Note"
    Since v4.3.0, if you set `Config\App::$allowedHostnames`, this returns the URL with the hostname set in it if the current URL matches.

!!! note "Note"
    In previous versions, this returned the base URL without a trailing slash (`/`) when called with no argument. The bug was fixed and since v4.3.2 it returns the base URL with a trailing slash.

Returns your site base URL, as specified in your config file. Example:

```php
<?php

echo base_url();
```

This function returns the same thing as `site_url()`, without the `Config\App::$indexPage` being appended.

Also like `site_url()`, you can supply segments as a string or an array. Here is a string example:

```php
<?php

// Returns like `http://example.com/blog/post/123`
echo base_url('blog/post/123');
```

The above example would return something like: **http://example.com/blog/post/123**

If you pass an empty string `''` as the second parameter, it returns the protocol-relative link:

```php
<?php

// Returns like `//example.com/blog/post/123`
echo base_url('blog/post/123', '');
```

This is useful because, unlike `site_url()`, you can supply a string to a file, such as an image or stylesheet. For example:

```php
<?php

echo base_url('images/icons/edit.png');
```

This would give you something like: **http://example.com/images/icons/edit.png**

#### current_url([$returnObject = false[, $request = null]])

- **Parameters**  
    - **$returnObject** `boolean` True if you would like a URI instance returned, instead of a string.
    - **$request** `IncomingRequest|null` An alternate request to use for path detection; useful for testing.

- **Returns**: The current URL

- **Return type**: `string|\CodeIgniter\HTTP\URI`

Returns the full URL of the page being currently viewed. When returning a string, the query and fragment parts of the URL are removed. When returning a URI, the query and fragment parts are preserved.

However, for security reasons, it is created based on the `Config\App` settings, and is not intended to match the browser URL.

Since v4.3.0, if you set `Config\App::$allowedHostnames`, this returns the URL with the hostname set in it if the current URL matches.

!!! note "Note"
    Calling `current_url()` is the same as doing this:

    ```php
    site_url(uri_string());
    ```

!!! important "Important"
    Prior to v4.1.2, this function had a bug causing it to ignore the configuration on `Config\App::$indexPage`.

#### previous_url([$returnObject = false])

- **Parameters**  
    - **$returnObject** `boolean` True if you would like a URI instance returned instead of a string.

- **Returns**: The URL the user was previously on

- **Return type**: `string|\CodeIgniter\HTTP\URI`

Returns the full URL (including segments) of the page the user was previously on.

!!! note "Note"
    Due to security issues of blindly trusting the `HTTP_REFERER` system variable, CodeIgniter will store previously visited pages in the session if it's available. This ensures that we always use a known and trusted source. If the session hasn't been loaded, or is otherwise unavailable, then a sanitized version of `HTTP_REFERER` will be used.

#### uri_string()

- **Returns**: A URI string
- **Return type**: `string`

Returns the path part of the current URL relative to baseURL.

For example, when your baseURL is **http://some-site.com/** and the current URL is:

```
http://some-site.com/blog/comments/123
```

The function would return:

```
blog/comments/123
```

When your baseURL is **http://some-site.com/subfolder/** and the current URL is:

```
http://some-site.com/subfolder/blog/comments/123
```

The function would return:

```
blog/comments/123
```

!!! note "Note"
    In previous versions, the parameter `$relative = false` was defined. However, due to a bug, this function always returned a path relative to baseURL. Since v4.3.2, the parameter has been removed.

!!! note "Note"
    In previous versions, when you navigated to the baseURL, this function returned `/`. Since v4.3.2, the bug has been fixed and it returns an empty string (`''`).

#### index_page([$altConfig = null])

- **Parameters**  
    - \Config\App `$altConfig` Alternate configuration to use

- **Returns**: The `indexPage` value

- **Return type**: `string`

Returns your site **indexPage**, as specified in your config file. Example:

```php
<?php

echo index_page();
```

As with `site_url()`, you may specify an alternate configuration. You may find the alternate configuration useful if generating URLs for a different site than yours, which contains different configuration preferences. We use this for unit testing the framework itself.

#### anchor([$uri = ''[, $title = ''[, $attributes = ''[, $altConfig = null]]]])

- **Parameters**  
    - **$uri** `array|string` URI string or array of URI segments
    - **$title** `string` Anchor title
    - **$attributes** `array|object|string` HTML attributes
    - \Config\App|null `$altConfig` Alternate configuration to use

- **Returns**: HTML hyperlink (anchor tag)

- **Return type**: `string`

Creates a standard HTML anchor link based on your local site URL.

The first parameter can contain any segments you wish appended to the URL. As with the `site_url()` function above, segments can be a string or an array.

!!! note "Note"
    If you are building links that are internal to your application, do not include the base URL (`http://...`). This will be added automatically from the information specified in your config file. Include only the URI segments you wish appended to the URL.

The second parameter is the text you would like the link to say. If you leave it blank, the URL will be used.

The third parameter can contain a list of attributes you would like added to the link. The attributes can be a simple string or an associative array.

Here are some examples:

```php
<?php

echo anchor('news/local/123', 'My News', 'title="News title"');
// Prints: <a href="http://example.com/index.php/news/local/123" title="News title">My News</a>

echo anchor('news/local/123', 'My News', ['title' => 'The best news!']);
// Prints: <a href="http://example.com/index.php/news/local/123" title="The best news!">My News</a>

echo anchor('', 'Click here');
// Prints: <a href="http://example.com/index.php">Click here</a>
```

As above, you may specify an alternate configuration. You may find the alternate configuration useful if generating links for a different site than yours, which contains different configuration preferences. We use this for unit testing the framework itself.

!!! note "Note"
    Attributes passed into the anchor function are automatically escaped to protect against XSS attacks.

#### anchor_popup([$uri = ''[, $title = ''[, $attributes = false[, $altConfig = null]]]])

- **Parameters**  
    - **$uri** `string` URI string
    - **$title** `string` Anchor title
    - **$attributes** `array|false|object|string` HTML attributes
    - \Config\App `$altConfig` Alternate configuration to use

- **Returns**: Pop-up hyperlink

- **Return type**: `string`

Nearly identical to the `anchor()` function except that it opens the URL in a new window. You can specify JavaScript window attributes in the third parameter to control how the window is opened. If the third parameter is not set, it will simply open a new window with your own browser settings.

Here is an example with attributes:

```php
<?php

$atts = [
    'width'       => 800,
    'height'      => 600,
    'scrollbars'  => 'yes',
    'status'      => 'yes',
    'resizable'   => 'yes',
    'screenx'     => 0,
    'screeny'     => 0,
    'window_name' => '_blank',
];

echo anchor_popup('news/local/123', 'Click Me!', $atts);
```

As above, you may specify an alternate configuration. You may find the alternate configuration useful if generating links for a different site than yours, which contains different configuration preferences. We use this for unit testing the framework itself.

!!! note "Note"
    The above attributes are the function defaults, so you only need to set the ones that are different from what you need. If you want the function to use all of its defaults, simply pass an empty array in the third parameter:

    ```php
    <?php

    echo anchor_popup('news/local/123', 'Click Me!', []);
    ```

!!! note "Note"
    The **window_name** is not really an attribute, but an argument to the JavaScript [window.open()](https://www.w3schools.com/jsref/met_win_open.asp) method, which accepts either a window name or a window target.

!!! note "Note"
    Any attribute other than those listed above will be parsed as an HTML attribute to the anchor tag.

!!! note "Note"
    Attributes passed into the anchor_popup function are automatically escaped to protect against XSS attacks.

#### mailto($email[, $title = ''[, $attributes = '']])

- **Parameters**  
    - **$email** `string` E-mail address
    - **$title** `string` Anchor title
    - **$attributes** `array|object|string` HTML attributes

- **Returns**: A "mail to" hyperlink

- **Return type**: `string`

Creates a standard HTML e-mail link. Usage example:

```php
<?php

echo mailto('me@my-site.com', 'Click Here to Contact Me');
```

As with the `anchor()` function above, you can set attributes using the third parameter:

```php
<?php

$attributes = ['title' => 'Mail me'];
echo mailto('me@my-site.com', 'Contact Me', $attributes);
```

!!! note "Note"
    Attributes passed into the mailto function are automatically escaped to protect against XSS attacks.

#### safe_mailto($email[, $title = ''[, $attributes = '']])

- **Parameters**  
    - **$email** `string` E-mail address
    - **$title** `string` Anchor title
    - **$attributes** `array|object|string` HTML attributes

- **Returns**: A spam-safe "mail to" hyperlink

- **Return type**: `string`

Identical to the `mailto()` function except it writes an obfuscated version of the *mailto* tag using ordinal numbers written with JavaScript to help prevent the e-mail address from being harvested by spam bots.

#### auto_link($str[, $type = 'both'[, $popup = false]])

- **Parameters**  
    - **$str** `string` Input string
    - **$type** `string` Link type (`'email'`, `'url'` or `'both'`)
    - **$popup** `bool` Whether to create popup links

- **Returns**: Linkified string

- **Return type**: `string`

Automatically turns URLs and e-mail addresses contained in a string into links. Example:

```php
<?php

$string = auto_link($string);
```

The second parameter determines whether URLs and e-mails are converted or just one or the other. The default behavior is both if the parameter is not specified. E-mail links are encoded as `safe_mailto()` as shown above.

Converts only URLs:

```php
<?php

$string = auto_link($string, 'url');
```

Converts only e-mail addresses:

```php
<?php

$string = auto_link($string, 'email');
```

The third parameter determines whether links are shown in a new window. The value can be true or false (boolean):

```php
<?php

$string = auto_link($string, 'both', true);
```

!!! note "Note"
    The only URLs recognized are those that start with `www.` or with `://`.

#### url_title($str[, $separator = '-'[, $lowercase = false]])

- **Parameters**  
    - **$str** `string` Input string
    - **$separator** `string` Word separator (usually `'-'` or `'_'`)
    - **$lowercase** `bool` Whether to transform the output string to lowercase

- **Returns**: URL-formatted string

- **Return type**: `string`

Takes a string as input and creates a human-friendly URL string. This is useful if, for example, you have a blog in which you'd like to use the title of your entries in the URL. Example:

```php
<?php

$title     = "What's wrong with CSS?";
$url_title = url_title($title);
// Produces: Whats-wrong-with-CSS
```

The second parameter determines the word delimiter. By default, dashes are used. Preferred options are: `-` (dash) or `_` (underscore).

Example:

```php
<?php

$title     = "What's wrong with CSS?";
$url_title = url_title($title, '_');
// Produces: Whats_wrong_with_CSS
```

The third parameter determines whether or not lowercase characters are forced. By default, they are not. Options are boolean true/false.

Example:

```php
<?php

$title     = "What's wrong with CSS?";
$url_title = url_title($title, '-', true);
// Produces: whats-wrong-with-css
```

#### mb_url_title($str[, $separator = '-'[, $lowercase = false]])

- **Parameters**  
    - **$str** `string` Input string
    - **$separator** `string` Word separator (usually `'-'` or `'_'`)
    - **$lowercase** `bool` Whether to transform the output string to lowercase

- **Returns**: URL-formatted string

- **Return type**: `string`

This function works the same as `url_title()` but it converts all accented characters automatically.

#### parse_subdomain([$host = null])

- **Parameters**  
    - **$host** `string|null` The hostname to parse. If null, uses the current request's host.

- **Returns**: The subdomain, or an empty string if none exists.

- **Return type**: `string`

Parses the subdomain from the given host name.

Here are some examples:

```php
<?php

// Outputs "blog"
echo parse_subdomain('blog.example.com');

// Outputs an empty string
echo parse_subdomain('example.com');
echo parse_subdomain('example.co.uk');

// Outputs "shop" - correctly handles two-part TLDs
echo parse_subdomain('shop.example.co.uk');

// Outputs "shop.old"
echo parse_subdomain('shop.old.example.co.uk');
```

You can customize the list of known two-part TLDs by adding them to the `Config\Hostnames::TWO_PART_TLDS` array.

#### prep_url([$str = ''[, $secure = false]])

- **Parameters**  
    - **$str** `string` URL string
    - **$secure** `boolean` true for `https://`

- **Returns**: Protocol-prefixed URL string

- **Return type**: `string`

This function will add `http://` or `https://` in the event that a protocol prefix is missing from a URL.

Pass the URL string to the function like this:

```php
<?php

$url = prep_url('example.com');
```

#### url_to($controller[, ...$args])

- **Parameters**  
    - **$controller** `string` Route name or `Controller::method`
    - int|string ...`$args` One or more parameters to be passed to the route. The last parameter allows you to set the locale.

- **Returns**: Absolute URL

- **Return type**: `string`

!!! note "Note"
    This function requires the controller/method to have a route defined in **app/Config/Routes.php**.

Builds an absolute URL to a controller method in your app. Example:

```php
<?php

// The route is defined as:
$routes->get('/', 'Home::index');

?>

<a href="<?= url_to('Home::index') ?>">Home</a>
<!-- Result: 'http://example.com/' -->
```

You can also add arguments to the route. Here is an example:

```php
<?php

// The route is defined as:
$routes->get('pages/(:segment)', 'Page::index/$1');

?>

<a href="<?= url_to('Page::index', 'home') ?>">Home</a>
<!-- Result: 'http://example.com/pages/home' -->
```

This is useful because you can still change your routes after putting links into your views.

Since v4.3.0, when you use `{locale}` in your route, you can optionally specify the locale value as the last parameter.

```php
<?php

// The route is defined as:
$routes->add(
    '{locale}/users/(:num)/gallery/(:num)',
    'Galleries::showUserGallery/$1/$2',
    ['as' => 'user_gallery'],
);

?>

<a href="<?= url_to('user_gallery', 15, 12, 'en') ?>">View Gallery</a>
<!-- Result: 'http://example.com/en/users/15/gallery/12' -->
```

For full details, see [Reverse Routing](../incoming/routing.md#reverse-routing) and [Named Routes](../incoming/routing.md#named-routes).

#### url_is($path)

- **Parameters**  
    - **$path** `string` The URL path relative to baseURL to check the current URI path against.

- **Return type**: `boolean`

Compares the current URL's path against the given path to see if they match. Example:

```php
<?php

if (url_is('admin')) {
    // ...
}
```

This would match **http://example.com/admin**. It would match **http://example.com/subdir/admin** if your baseURL is `http://example.com/subdir/`.

You can use the `*` wildcard to match any other applicable characters in the URL:

```php
<?php

if (url_is('admin*')) {
    // ...
}
```

This would match any of the following:

- /admin
- /admin/
- /admin/users
- /admin/users/schools/classmates/...
