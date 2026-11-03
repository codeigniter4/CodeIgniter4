# Cookie Helper

The Cookie Helper file contains functions that assist in working with cookies.

## Loading this Helper

This helper is loaded using the following code:

```php
<?php

helper('cookie');
```

## Available Functions

The following functions are available:

#### set_cookie($name[, $value = ''[, $expire = 0[, $domain = ''[, $path = '/'[, $prefix = ''[, $secure = false[, $httpOnly = false[, $sameSite = '']]]]]]]])

- **Parameters**  
    - **$name** `array|Cookie|string` Cookie name *or* associative array of all of the parameters available to this function *or* an instance of `CodeIgniter\Cookie\Cookie`
    - **$value** `string` Cookie value
    - **$expire** `int` Number of seconds until expiration. If set to `0`, the cookie will only last as long as the browser is open
    - **$domain** `string` Cookie domain (usually: .yourdomain.com)
    - **$path** `string` Cookie path
    - **$prefix** `string` Cookie name prefix. If `''`, the default from **app/Config/Cookie.php** is used
    - **$secure** `bool` Whether to only send the cookie through HTTPS. If `null`, the default from **app/Config/Cookie.php** is used
    - **$httpOnly** `bool` Whether to hide the cookie from JavaScript. If `null`, the default from **app/Config/Cookie.php** is used
    - **$sameSite** `string` The value for the SameSite cookie parameter. If `null`, the default from **app/Config/Cookie.php** is used

- **Return type**: `void`

!!! note "Note"
    Prior to v4.2.7, the default values of `$secure` and `$httpOnly` were `false` due to a bug, and these values from **app/Config/Cookie.php** were never used.

This helper function gives you friendlier syntax to set browser cookies. Refer to the [Response Library](../outgoing/response.md) for a description of its use, as this function is an alias for `CodeIgniter\HTTP\Response::setCookie()`.

!!! note "Note"
    This helper function just sets browser cookies to the global response instance that `Services::response()` returns. So, if you create and return another response instance (e.g., if you call `redirect()`), the cookies set here will not be sent automatically.

#### get_cookie($index[, $xssClean = false[, $prefix = '']])

- **Parameters**  
    - **$index** `string` Cookie name
    - **$xssClean** `bool` Whether to apply XSS filtering to the returned value
    - **$prefix** `string|null` Cookie name prefix. If set to `''`, the default value from **app/Config/Cookie.php** will be used. If set to `null`, no prefix is used

- **Returns**: The cookie value or null if not found

- **Return type**: `mixed`

!!! note "Note"
    Since v4.2.1, the third parameter `$prefix` has been introduced and the behavior has been changed a bit due to a bug fix. See [Upgrading](../installation/upgrade_421.md#get_cookie) for details.

This helper function gives you friendlier syntax to get browser cookies. Refer to the [IncomingRequest Library](../incoming/incomingrequest.md) for a detailed description of its use, as this function acts very similarly to `CodeIgniter\HTTP\IncomingRequest::getCookie()`, except it will also prepend the `Config\Cookie::$prefix` that you might've set in your **app/Config/Cookie.php** file.

!!! warning "Warning"
    Using XSS filtering is a bad practice. It does not prevent XSS attacks perfectly. Using `esc()` with the correct `$context` in the views is recommended.

#### delete_cookie($name[, $domain = ''[, $path = '/'[, $prefix = '']]])

- **Parameters**  
    - **$name** `string` Cookie name
    - **$domain** `string` Cookie domain (usually: .yourdomain.com)
    - **$path** `string` Cookie path
    - **$prefix** `string` Cookie name prefix

- **Return type**: `void`

Lets you delete a cookie. Unless you've set a custom path or other values, only the name of the cookie is needed.

```php
<?php

delete_cookie('name');
```

This function is otherwise identical to `set_cookie()`, except that it does not have the `value` and `expire` parameters.

This also just sets the deletion cookies on the global response instance that `Services::response()` returns.

!!! note "Note"
    When you use `set_cookie()`, if the `value` is set to an empty string and the `expire` is set to `0`, the cookie will be deleted. If the `value` is set to a non-empty string and the `expire` is set to `0`, the cookie will only last as long as the browser is open.

You can submit an array of values in the first parameter, or you can set discrete parameters.

```php
<?php

delete_cookie($name, $domain, $path, $prefix);
```

#### has_cookie(string $name[, ?string $value = null[, string $prefix = '']])

- **Parameters**  
    - **$name** `string` Cookie name
    - **$value** `string|null` Cookie value
    - **$prefix** `string` Cookie prefix

- **Return type**: `bool`

Checks if a cookie exists by name in the global response instance that `Services::response()` returns. This is an alias of `CodeIgniter\HTTP\Response::hasCookie()`.
