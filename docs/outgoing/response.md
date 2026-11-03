# HTTP Responses

The Response class extends the [HTTP Message Class](../incoming/message.md) with methods appropriate only for a server responding to the client that called it.

## Working with the Response

A Response class is instantiated for you and passed into your controllers. It can be accessed through `$this->response`. It is the same instance that `Services::response()` returns. We call it the global response instance.

Many times you will not need to touch the class directly, since CodeIgniter takes care of sending the headers and the body for you. This is great if the page successfully created the content it was asked to. When things go wrong, or you need to send very specific status codes back, or even take advantage of the powerful HTTP caching, it's there for you.

### Setting the Output

When you need to set the output of the script directly, and not rely on CodeIgniter to automatically get it, you do it manually with the `setBody` method. This is usually used in conjunction with setting the status code of the response:

```php
<?php

$this->response->setStatusCode(404)->setBody($body);
```

The reason phrase ('OK', 'Created', 'Moved Permanently') will be automatically added, but you can add custom reasons as the second parameter of the `setStatusCode()` method:

```php
<?php

$this->response->setStatusCode(404, 'Nope. Not here.');
```

You can format an array into either JSON or XML and set the content type header to the appropriate MIME type with the `setJSON()` and `setXML()` methods. Typically, you will send an array of data to be converted:

```php
<?php

$data = [
    'success' => true,
    'id'      => 123,
];

return $this->response->setJSON($data);

// or
return $this->response->setXML($data);
```

### Setting Headers

#### setHeader()

Often, you will need to set headers for the response. The Response class makes this very simple to do, with the `setHeader()` method.

The first parameter is the name of the header. The second parameter is the value, which can be either a string or an array of values that will be combined correctly when sent to the client.

```php
<?php

$this->response->setHeader('Location', 'http://example.com')
    ->setHeader('WWW-Authenticate', 'Negotiate');
```

Using these methods instead of the native PHP functions ensures that no headers are sent prematurely, causing errors, and makes testing possible.

!!! important "Important"
    Since v4.6.0, if you set a header using PHP's native `header()` function and then use the `Response` class to set the same header, the previous one will be overwritten.

!!! note "Note"
    This method just sets headers on the response instance. So, if you create and return another response instance (e.g., if you call `redirect()`), the headers set here will not be sent automatically.

#### appendHeader()

If the header exists and can have more than one value, you may use the `appendHeader()` and `prependHeader()` methods to add the value to the end or beginning of the values list, respectively. The first parameter is the name of the header, while the second is the value to append or prepend.

```php
<?php

$this->response->setHeader('Cache-Control', 'no-cache')
    ->appendHeader('Cache-Control', 'must-revalidate');
```

#### removeHeader()

Headers can be removed from the response with the `removeHeader()` method, which takes the header name as the only parameter. This is not case-sensitive.

```php
<?php

$this->response->removeHeader('Location');
```

## Redirect

If you want to create a redirect, use the `redirect()` function.

It returns a `RedirectResponse` instance. It is a different instance from the global response instance that `Services::response()` returns.

!!! warning "Warning"
    If you set Cookies or Response Headers before you call `redirect()`, they are set on the global response instance, and they are not automatically copied to the `RedirectResponse` instance. To send them, you need to call the `withCookies()` or `withHeaders()` method manually.

!!! important "Important"
    If you want to redirect, an instance of `RedirectResponse` must be returned in a method of the [Controller](../incoming/controllers.md) or the [Controller Filter](../incoming/filters.md). Note that the `__construct()` or the `initController()` method cannot return any value. If you forget to return `RedirectResponse`, no redirection will occur.

### Redirect to a URI path

When you want to pass a URI path (relative to baseURL), use `redirect()->to()`:

```php
// Go to specific URI path. "admin/home" is the URI path relative to baseURL.
return redirect()->to('admin/home');
```

!!! note "Note"
    If there is a fragment in your URL that you want to remove, you can use the refresh parameter in the method. For example, `return redirect()->to('admin/home', null, 'refresh');`.

### Redirect to a Defined Route

When you want to pass a [route name](../incoming/routing.md#named-routes) or `Controller::method` for [reverse routing](../incoming/routing.md#reverse-routing), use `redirect()->route()`:

```php
// Go to a named route. "user_gallery" is the route name, not a URI path.
return redirect()->route('user_gallery');
```

An argument passed into the function is treated as a route name or `Controller::method` for reverse routing, not a relative/full URI, the same as when using `redirect()->route()`:

```php
// Go to a named/reverse-routed URI.
return redirect('named_route');
```

### Redirect Back

When you want to redirect back, use `redirect()->back()`:

```php
// Go back to the previous page.
return redirect()->back();

// Keep the old input values upon redirect so they can be used by the `old()` function.
return redirect()->back()->withInput();

// Set a flash message.
return redirect()->back()->with('foo', 'message');
```

!!! note "Note"
    `redirect()->back()` is not the same as the browser "back" button. It takes a visitor to "the last page viewed during the Session" when the Session is available. If the Session hasn't been loaded, or is otherwise unavailable, then a sanitized version of HTTP_REFERER will be used.

### Redirect with Cookies

If you set Cookies before you call `redirect()`, they are set on the global response instance, and they are not automatically copied to the `RedirectResponse` instance.

To send the Cookies, you need to call the `withCookies()` method manually.

```php
// Copies all cookies from global response instance.
return redirect()->back()->withCookies();
```

### Redirect with Headers

If you set Response Headers before you call `redirect()`, they are set on the global response instance, and they are not automatically copied to the `RedirectResponse` instance.

To send the Headers, you need to call the `withHeaders()` method manually.

```php
// Copies all headers from the global response instance.
return redirect()->back()->withHeaders();
```

### Redirect Status Code

The default HTTP status code for GET requests is 302. However, when using HTTP/1.1 or later, 303 is used for POST/PUT/DELETE requests and 307 for all other requests.

You can specify the status code:

```php
// Redirect to a URI path relative to baseURL with status code 301.
return redirect()->to('admin/home', 301);

// Redirect to a route with status code 308.
return redirect()->route('user_gallery', [], 308);

// Redirect back with status code 302.
return redirect()->back(302);
```

!!! note "Note"
    Due to a bug, in v4.3.3 or previous versions, the status code of the actual redirect response might be changed even if a status code was specified. See [ChangeLog v4.3.4](../changelogs/v4.3.4.md#redirect-status-code).

If you don't know the HTTP status codes for redirection, it is recommended to read [Redirections in HTTP](https://developer.mozilla.org/en-US/docs/Web/HTTP/Redirections).

## Force File Download

The Response class provides a simple way to send a file to the client, prompting the browser to download the data to your computer. This sets the appropriate headers to make it happen.

The first parameter is the **name you want to give the downloaded file**, and the second parameter is the file data.

If you set the second parameter to null and `$filename` is an existing, readable file path, then its content will be read instead.

If you set the third parameter to boolean true, then the actual file MIME type (based on the filename extension) will be sent, so that if your browser has a handler for that type, it can use it.

Example:

```php
<?php

$data = 'Here is some text!';
$name = 'mytext.txt';

return $this->response->download($name, $data);
```

If you want to download an existing file from your server, you'll need to pass `null` explicitly for the second parameter:

```php
<?php

// Contents of photo.jpg will be automatically read
return $this->response->download('/path/to/photo.jpg', null);
```

Use the optional `setFileName()` method to change the filename as it is sent to the client's browser:

```php
<?php

return $this->response->download('awkwardEncryptedFileName.fakeExt', null)->setFileName('expenses.csv');
```

!!! note "Note"
    The response object MUST be returned for the download to be sent to the client. This allows the response to be passed through all **after** filters before being sent to the client.

### Open File in Browser

Some browsers can display files such as PDF. To tell the browser to display the file instead of saving it, call the `DownloadResponse::inline()` method.

```php
<?php

$data = 'Here is some text!';
$name = 'mytext.txt';

return $this->response->download($name, $data)->inline();
```

## HTTP Caching

Built into the HTTP specification are tools that help the client (often the web browser) cache the results. Used correctly, this can lead to a huge performance boost to your application because it will tell the client that they don't need to contact the server at all since nothing has changed. And you can't get faster than that.

This is handled through the `Cache-Control` and `ETag` headers. This guide is not the proper place for a thorough introduction to the power of all the cache headers, but you can get a good understanding over at [Google Developers](https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/http-caching).

By default, all response objects sent through CodeIgniter have HTTP caching turned off. The options and exact circumstances are too varied for us to be able to create a good default other than turning it off. It's simple to set the Cache values to what you need, through the `setCache()` method:

```php
<?php

$options = [
    'max-age'  => 300,
    's-maxage' => 900,
    'etag'     => 'abcde',
];
$this->response->setCache($options);
```

The `$options` array simply takes an array of key/value pairs that are, with a couple of exceptions, assigned to the `Cache-Control` header. You are free to set all of the options exactly as you need for your specific situation. While most of the options are applied to the `Cache-Control` header, it intelligently applies the `etag` and `last-modified` options to their appropriate headers.

## Class Reference

!!! note "Note"
    In addition to the methods listed here, this class inherits the methods from the [Message Class](../incoming/message.md).

The methods inherited from the Message Class are:

- `CodeIgniter\\HTTP\\Message::getBody`
- `CodeIgniter\\HTTP\\Message::setBody`
- `CodeIgniter\\HTTP\\Message::populateHeaders`
- `CodeIgniter\\HTTP\\Message::headers`
- `CodeIgniter\\HTTP\\Message::header`
- `CodeIgniter\\HTTP\\Message::getHeaderLine`
- `CodeIgniter\\HTTP\\Message::setHeader`
- `CodeIgniter\\HTTP\\Message::removeHeader`
- `CodeIgniter\\HTTP\\Message::appendHeader`
- `CodeIgniter\\HTTP\\Message::getProtocolVersion`
- `CodeIgniter\\HTTP\\Message::setProtocolVersion`

## CodeIgniter\HTTP

### Response

#### getStatusCode()

- **Returns**: The current HTTP status code for this response
- **Return type**: `int`

Returns the current status code for this response. If no status code has been set, an `HTTPException` will be thrown:

```php
<?php

echo $response->getStatusCode();
```

#### setStatusCode($code[, $reason=''])

- **Parameters**  
    - **$code** `int` The HTTP status code
    - **$reason** `string` An optional reason phrase.

- **Returns**: The current Response instance

- **Return type**: `CodeIgniter\HTTP\Response`

Sets the HTTP status code that should be sent with this response:

```php
<?php

$response->setStatusCode(404);
```

The reason phrase will be automatically generated based upon the official lists. If you need to set your own for a custom status code, you can pass the reason phrase as the second parameter:

```php
<?php

$response->setStatusCode(230, 'Tardis initiated');
```

#### getReasonPhrase()

- **Returns**: The current reason phrase.
- **Return type**: `string`

Returns the current reason phrase for this response. If no status has been set, it will return an empty string:

```php
<?php

echo $response->getReasonPhrase();
```

#### setDate($date)

- **Parameters**  
    - **$date** `DateTime` A DateTime instance with the time to set for this response.

- **Returns**: The current response instance.

- **Return type**: `CodeIgniter\HTTP\Response`

Sets the date used for this response. The `$date` argument must be an instance of `DateTime`.

#### setContentType($mime[, $charset='UTF-8'])

- **Parameters**  
    - **$mime** `string` The content type this response represents.
    - **$charset** `string` The character set this response uses.

- **Returns**: The current response instance.

- **Return type**: `CodeIgniter\HTTP\Response`

Sets the content type this response represents:

```php
<?php

$response->setContentType('text/plain');
$response->setContentType('text/html');
$response->setContentType('application/json');
```

By default, the method sets the character set to `UTF-8`. If you need to change this, you can pass the character set as the second parameter:

```php
<?php

$response->setContentType('text/plain', 'x-pig-latin');
```

#### noCache()

- **Returns**: The current response instance.
- **Return type**: `CodeIgniter\HTTP\Response`

Sets the `Cache-Control` header to turn off all HTTP caching. This is the default setting of all response messages:

```php
<?php

$response->noCache();
/*
 * Sets the following header:
 * Cache-Control: no-store, max-age=0, no-cache
 */
```

#### setCache($options)

- **Parameters**  
    - **$options** `array` An array of key/value cache control settings

- **Returns**: The current response instance.

- **Return type**: `CodeIgniter\HTTP\Response`

Sets the `Cache-Control` headers, including `ETags` and `Last-Modified`. Typical keys are:

- etag
- last-modified
- max-age
- s-maxage
- private
- public
- must-revalidate
- proxy-revalidate
- no-transform

When passing the last-modified option, it can be either a date string or a DateTime object.

#### setLastModified($date)

- **Parameters**  
    - **$date** `string|DateTime` The date to set the Last-Modified header to

- **Returns**: The current response instance.

- **Return type**: `CodeIgniter\HTTP\Response`

Sets the `Last-Modified` header. The `$date` argument can be either a string or a `DateTime` instance:

```php
<?php

$response->setLastModified(date('D, d M Y H:i:s'));
$response->setLastModified(\DateTime::createFromFormat('!U', $timestamp));
```

#### send(): Response

- **Returns**: The current response instance.
- **Return type**: `CodeIgniter\HTTP\Response`

Tells the response to send everything back to the client. This will first send the headers, followed by the response body. For the main application response, you do not need to call this as it is handled automatically by CodeIgniter.

#### setCookie($name = ''[, $value = ''[, $expire = 0[, $domain = ''[, $path = '/'[, $prefix = ''[, $secure = null[, $httponly = null[, $samesite = null]]]]]]]])

- **Parameters**  
    - **$name** `array|Cookie|string` Cookie name *or* associative array of all of the parameters available to this method *or* an instance of `CodeIgniter\Cookie\Cookie`
    - **$value** `string` Cookie value
    - **$expire** `int` Cookie expiration time in seconds. If set to `0` the cookie will only last as long as the browser is open
    - **$domain** `string` Cookie domain
    - **$path** `string` Cookie path
    - **$prefix** `string` Cookie name prefix. If set to `''`, the default value from **app/Config/Cookie.php** will be used
    - **$secure** `bool|null` Whether to only transfer the cookie through HTTPS. If set to `null`, the default value from **app/Config/Cookie.php** will be used
    - **$httponly** `bool|null` Whether to only make the cookie accessible for HTTP requests (no JavaScript). If set to `null`, the default value from **app/Config/Cookie.php** will be used
    - **$samesite** `string|null` The value for the SameSite cookie parameter. If set to `''` or `null`, the default value from **app/Config/Cookie.php** will be used

- **Return type**: `void`

!!! note "Note"
    Prior to v4.2.7, the default values of `$secure` and `$httponly` were `false` due to a bug, and these values from **app/Config/Cookie.php** were never used.

Sets a cookie containing the values you specify to the Response instance.

There are two ways to pass information to this method so that a cookie can be set: Array Method, and Discrete Parameters:

**Array Method**

Using this method, an associative array is passed as the first parameter:

```php
<?php

$cookie = [
    'name'     => 'The Cookie Name',
    'value'    => 'The Value',
    'expire'   => 86500,
    'domain'   => '.some-domain.com',
    'path'     => '/',
    'prefix'   => 'myprefix_',
    'secure'   => true,
    'httponly' => false,
    'samesite' => 'Lax',
];

$response->setCookie($cookie);
```

Only the `name` and `value` are required. To delete a cookie, set it with the `value` blank.

The `expire` is set in **seconds**, which will be added to the current time. Do not include the time, but rather only the number of seconds from *now* that you wish the cookie to be valid. If the `expire` is set to zero, the cookie will only last as long as the browser is open.

!!! note "Note"
    However, if the `value` is set to an empty string and the `expire` is set to `0`, the cookie will be deleted.

For site-wide cookies regardless of how your site is requested, add your URL to the `domain` starting with a period, like this: `.your-domain.com`.

The `path` is usually not needed since the method sets a root path.

The `prefix` is only needed if you need to avoid name collisions with other identically named cookies for your server.

The `secure` flag is only needed if you want to make it a secure cookie by setting it to `true`.

The `samesite` value controls how cookies are shared between domains and sub-domains. Allowed values are `'None'`, `'Lax'`, `'Strict'` or a blank string `''`. If set to a blank string, the default SameSite attribute will be set.

**Discrete Parameters**

If you prefer, you can set the cookie by passing data using individual parameters:

```php
<?php

$response->setCookie($name, $value, $expire, $domain, $path, $prefix, $secure, $httponly, $samesite);
```

#### deleteCookie($name = ''[, $domain = ''[, $path = '/'[, $prefix = '']]])

- **Parameters**  
    - **$name** `mixed` Cookie name or an array of parameters
    - **$domain** `string` Cookie domain
    - **$path** `string` Cookie path
    - **$prefix** `string` Cookie name prefix

- **Return type**: `void`

Deletes an existing cookie.

!!! note "Note"
    This also just sets a browser cookie for deleting the cookie.

Only the `name` is required.

The `prefix` is only needed if you need to avoid name collisions with other identically named cookies for your server.

Provide a `prefix` if cookies should only be deleted for that subset. Provide a `domain` name if cookies should only be deleted for that domain. Provide a `path` name if cookies should only be deleted for that path.

If any of the optional parameters are empty, then the same-named cookie will be deleted across all that apply.

Example:

```php
<?php

$response->deleteCookie($name);
```

#### hasCookie($name = ''[, $value = null[, $prefix = '']])

- **Parameters**  
    - **$name** `mixed` Cookie name or an array of parameters
    - **$value** `string` Cookie value
    - **$prefix** `string` Cookie name prefix

- **Return type**: `bool`

Checks to see if the Response has a specified cookie or not.

**Notes**

Only the `name` is required. If a `prefix` is specified, it will be prepended to the cookie name.

If no `value` is given, the method just checks for the existence of the named cookie. If a `value` is given, then the method checks that the cookie exists, and that it has the prescribed value.

Example:

```php
<?php

if ($response->hasCookie($name)) {
    // ...
}
```

#### getCookie($name = ''[, $prefix = ''])

- **Parameters**  
    - **$name** `string` Cookie name
    - **$prefix** `string` Cookie name prefix

- **Return type**: `Cookie|Cookie[]|null`

Returns the named cookie, if found, or `null`. If no `name` is given, returns the array of `Cookie` objects.

Example:

```php
<?php

$cookie = $response->getCookie($name);
```

#### getCookies()

- **Return type**: `Cookie[]`

Returns all cookies currently set within the Response instance. These are any cookies that you have specifically set during the current request only.
