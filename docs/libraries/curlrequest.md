# CURLRequest Class

The `CURLRequest` class is a lightweight HTTP client based on CURL that allows you to talk to other websites and servers. It can be used to get the contents of a Google search, retrieve a web page or image, or communicate with an API, among many other things.

This class is modeled after the [Guzzle HTTP Client](http://docs.guzzlephp.org/en/latest/) library since it is one of the more widely used libraries. Where possible, the syntax has been kept the same so that if your application needs something a little more powerful than what this library provides, you will have to change very little to move over to use Guzzle.

!!! note "Note"
    This class requires the [cURL Library](https://www.php.net/manual/en/book.curl.php) to be installed in your version of PHP. This is a very common library that is typically available, but not all hosts will provide it, so please check with your host to verify if you run into problems.

## Config for CURLRequest

### Sharing Connection

!!! success "Available from version 4.7.0"

By default, this option is enabled with the constants `CURL_LOCK_DATA_CONNECT` and `CURL_LOCK_DATA_DNS`.

If you want to share connections between requests, set `$shareConnectionOptions` to an array of [CURL_LOCK_DATA_\*](https://www.php.net/manual/en/curl.constants.php#constant.curl-lock-data-connect) constants in **app/Config/CURLRequest.php**:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class CURLRequest extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * CURLRequest Share Connection Options
     * --------------------------------------------------------------------------
     *
     * Share connection options between requests.
     *
     * @var list<int>
     *
     * @see https://www.php.net/manual/en/curl.constants.php#constant.curl-lock-data-connect
     */
    public array $shareConnection = [
        CURL_LOCK_DATA_CONNECT,
        CURL_LOCK_DATA_DNS,
    ];

    // ...
}
```

Or, when you want to disable it, just change it to an empty array:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class CURLRequest extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * CURLRequest Share Connection Options
     * --------------------------------------------------------------------------
     *
     * Share connection options between requests.
     *
     * @var list<int>
     *
     * @see https://www.php.net/manual/en/curl.constants.php#constant.curl-lock-data-connect
     */
    public array $shareConnection = [];

    // ...
}
```

### Sharing Options

!!! important "Important"
    This setting exists only for backward compatibility. Do not use it in new projects. Even if you are already using it, we recommend that you disable it.

!!! note "Note"
    Since v4.4.0, the default value has been changed to `false`.

If you want to share all the options between requests, set `$shareOptions` to `true` in **app/Config/CURLRequest.php**:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class CURLRequest extends BaseConfig
{
    // ...
    public bool $shareOptions = true;
}
```

If you send more than one request with an instance of the class, this behavior may result in an erroneous request with unnecessary headers and body.

!!! note "Note"
    Before v4.2.0, the request body was not reset even if `$shareOptions` was false, due to a bug.

## Loading the Library

The library can be loaded either manually or through the [Services class](../concepts/services.md).

To load it with the Services class, call the `curlrequest()` method or the global function `service()`:

```php
<?php

$client = service('curlrequest'); // Since v4.5.0, this code is recommended due to performance improvements

// The code above is the same as the code below.
$client = \Config\Services::curlrequest();
```

You can pass in an array of default options as the first parameter to modify how cURL will handle the request. The options are described later in this document:

```php
<?php

$options = [
    'baseURI' => 'http://example.com/api/v1/',
    'timeout' => 3,
];
$client = service('curlrequest', $options);
```

!!! note "Note"
    When `$shareOptions` is false, the default options passed to the class constructor will be used for all requests. Other options will be reset after sending a request.

When creating the class manually, you need to pass a few dependencies in. The first parameter is an instance of the `Config\App` class. The second parameter is a URI instance. The third parameter is a Response object. The fourth parameter is the optional default `$options` array:

```php
<?php

use Config\App;

$client = new \CodeIgniter\HTTP\CURLRequest(
    config(App::class),
    new \CodeIgniter\HTTP\URI(),
    new \CodeIgniter\HTTP\Response(config(App::class)),
    $options,
);
```

## Working with the Library

Working with CURL requests is simply a matter of creating the Request and getting a [Response object](../outgoing/response.md) back. It is meant to handle the communications. After that, you have complete control over how the information is handled.

### Making Requests

Most communication is done through the `request()` method, which fires off the request, and then returns a Response instance to you. This takes the HTTP method, the URL, and an array of options as the parameters.

```php
<?php

$client = service('curlrequest');

$response = $client->request('GET', 'https://api.github.com/user', [
    'auth' => ['user', 'pass'],
]);
```

!!! important "Important"
    By default, CURLRequest will throw `HTTPException` if the HTTP code returned is greater than or equal to 400. If you want to get the response, see the [http_errors](#http_errors) option.

!!! note "Note"
    When `$shareOptions` is false, the options passed to the method will be used for the request. After the request succeeds or throws an exception, they will be cleared, including per-request `baseURI` and `delay` values. The constructor defaults will be restored. To retry a failed request, pass its options again and reapply any settings made with `setAuth()`, `setBody()`, `setForm()`, or `setJSON()`. If you want to use the options for all requests, pass the options in the constructor.

Since the response is an instance of `CodeIgniter\HTTP\Response`, you have all of the normal information available to you:

```php
<?php

echo $response->getStatusCode();
echo $response->getBody();
echo $response->header('Content-Type');
$language = $response->negotiateLanguage(['en', 'fr']);
```

While the `request()` method is the most flexible, you can also use the following shortcut methods. They each take the URL as the first parameter and an array of options as the second:

```php
<?php

$client->get('http://example.com');
$client->delete('http://example.com');
$client->head('http://example.com');
$client->options('http://example.com');
$client->patch('http://example.com');
$client->put('http://example.com');
$client->post('http://example.com');
```

#### Base URI

A `baseURI` can be set as one of the options during the instantiation of the class. This allows you to set a base URI, and then make all requests with that client using relative URLs. This is especially handy when working with APIs:

```php
<?php

$client = service('curlrequest', [
    'baseURI' => 'https://example.com/api/v1/',
]);

// GET http:example.com/api/v1/photos
$client->get('photos');

// GET http:example.com/api/v1/photos/13
$client->delete('photos/13');
```

When a relative URI is provided to the `request()` method or any of the shortcut methods, it will be combined with the baseURI according to the rules described by [RFC 3986, section 5.2](https://tools.ietf.org/html/rfc3986#section-5.2). To save you some time, here are some examples of how the combinations are resolved.

| baseURI             | URI            | Result                 |
|---------------------|----------------|------------------------|
| http://foo.com      | /bar           | http://foo.com/bar     |
| http://foo.com/foo  | /bar           | http://foo.com/bar     |
| http://foo.com/foo  | bar            | http://foo.com/bar     |
| http://foo.com/foo/ | bar            | http://foo.com/foo/bar |
| http://foo.com      | http://baz.com | http://baz.com         |
| http://foo.com/?bar | bar            | http://foo.com/bar     |

### Using Responses

Each `request()` call returns a Response object that contains a lot of useful information and some helpful methods. The most commonly used methods let you determine the response itself.

You can get the status code and reason phrase of the response:

```php
<?php

$code   = $response->getStatusCode();   // 200
$reason = $response->getReasonPhrase(); // OK
```

You can retrieve headers from the response:

```php
<?php

// Get a header line
echo $response->getHeaderLine('Content-Type');

// Get all headers
foreach ($response->headers() as $name => $value) {
    echo $name . ': ' . $response->getHeaderLine($name) . "\n";
}
```

The body can be retrieved using the `getBody()` method:

```php
<?php

$body = $response->getBody();
```

The body is the raw body provided by the remote server. If the content type requires formatting, you will need to ensure that your script handles that:

```php
<?php

if (str_contains($response->header('content-type'), 'application/json')) {
    $body = json_decode($body);
}
```

## Request Options

This section describes all of the available options you may pass into the constructor, the `request()` method, or any of the shortcut methods.

### allow_redirects

By default, cURL will not follow any "Location:" headers the remote servers send back. The `allow_redirects` option allows you to modify how that works.

If you set the value to `true`, then it will follow redirects:

```php
<?php

$client->request('GET', 'http://example.com', ['allow_redirects' => true]);
/*
 * Sets the following defaults:
 *   'max'       => 5,                // Maximum number of redirects to follow before stopping
 *   'strict'    => true,             // Ensure POST requests stay POST requests through redirects
 *   'protocols' => ['http', 'https'] // Restrict redirects to one or more protocols
 */
```

!!! warning "Warning"
    Please note that enabling redirects may redirect to a URL that you do not expect and may enable SSRF attacks.

Setting it to `false` will apply the default settings to the request:

```php
<?php

$client->request('GET', 'http://example.com', ['allow_redirects' => false]);
```

You can pass in an array as the value of the `allow_redirects` option to specify new settings in place of the defaults:

```php
<?php

$client->request('GET', 'http://example.com', ['allow_redirects' => [
    'max'       => 10,
    'protocols' => ['https'], // Force HTTPS domains only.
]]);
```

!!! note "Note"
    Following redirects does not work when PHP's `open_basedir` restriction is enabled.

### auth

Allows you to provide Authentication details for [HTTP Basic](https://www.ietf.org/rfc/rfc2069.txt) and [Digest](https://www.ietf.org/rfc/rfc2069.txt) authentication. Your script may have to do extra work to support Digest authentication - this simply passes the username and password along for you. The value must be an array where the first element is the username, and the second is the password. The third parameter should be the type of authentication to use, either `basic` or `digest`:

```php
<?php

$client->request('GET', 'http://example.com', ['auth' => ['username', 'password', 'digest']]);
```

### body

There are two ways to set the body of the request for request types that support it, like PUT or POST. The first way is to use the `setBody()` method:

```php
<?php

$client->setBody($body)->request('PUT', 'http://example.com');
```

The second method is by passing a `body` option in. This is provided to maintain Guzzle API compatibility, and functions the exact same way as the previous example. The value must be a string:

```php
<?php

$client->request('PUT', 'http://example.com', ['body' => $body]);
```

### cert

To specify the location of a PEM-formatted client-side certificate, pass a string with the full path to the file as the `cert` option. If a password is required, set the value to an array with the first element as the path to the certificate, and the second as the password:

```php
<?php

$client->request('GET', '/', ['cert' => ['/path/server.pem', 'password']]);
```

### connect_timeout

By default, CodeIgniter does not impose a limit for cURL to attempt to connect to a website. If you need to modify this value, you can do so by passing the amount of time in seconds with the `connect_timeout` option. You can pass 0 to wait indefinitely:

```php
<?php

$client->request('GET', 'http://example.com', ['connect_timeout' => 0]);
```

### cookie

This specifies the filename that CURL should use to read cookie values from, and to save cookie values to. This is done using the `CURLOPT_COOKIEJAR` and `CURLOPT_COOKIEFILE` options. An example:

```php
<?php

$client->request('GET', 'http://example.com', ['cookie' => WRITEPATH . 'CookieSaver.txt']);
```

### debug

When `debug` is passed and set to `true`, this will enable additional debugging to echo to STDERR during the script execution.

This is done by passing `CURLOPT_VERBOSE` and echoing the output. So, when you're running a built-in server via `spark serve`, you will see the output in the console. Otherwise, the output will be written to the server's error log.

```php
<?php

$client->request('GET', 'http://example.com', ['debug' => true]);
```

You can pass a filename as the value for debug to have the output written to a file:

```php
<?php

$client->request('GET', 'http://example.com', ['debug' => '/usr/local/curl_log.txt']);
```

### delay

Allows you to pause for a number of milliseconds before sending the request:

```php
<?php

// Delay for 2 seconds
$client->request('GET', 'http://example.com', ['delay' => 2000]);
```

### dns_cache_timeout

!!! success "Available from version 4.7.0"

By default, CodeIgniter does not change the DNS Cache Timeout value (`120` seconds). If you need to modify this value, you can do so by passing an amount of time in seconds with the `dns_cache_timeout` option.

```php
<?php

// Modify default DNS Cache Timeout
$client->request('GET', '/', ['dns_cache_timeout' => 360]); // seconds
```

!!! note "Note"
    Based on the [libcurl](https://curl.se/libcurl/c/CURLOPT_DNS_CACHE_TIMEOUT.html) documentation, you can set it to zero (`0`) to completely disable caching, or set to `-1` to make the cached entries remain forever.

### form_params

You can send form data in an application/x-www-form-urlencoded POST request by passing an associative array in the `form_params` option. This will set the `Content-Type` header to `application/x-www-form-urlencoded` if it's not already set:

```php
<?php

$client->request('POST', '/post', [
    'form_params' => [
        'foo' => 'bar',
        'baz' => ['hi', 'there'],
    ],
]);
```

!!! note "Note"
    `form_params` cannot be used with the [multipart](#multipart) option. You will need to use one or the other. Use `form_params` for `application/x-www-form-urlencoded` requests, and `multipart` for `multipart/form-data` requests.

### fresh_connect

!!! success "Available from version 4.7.0"

By default, the request is sent using a fresh connection. You can disable this behavior using the `fresh_connect` option:

```php
<?php

$client->request('GET', 'http://example.com', ['fresh_connect' => true]);
$client->request('GET', 'http://example.com', ['fresh_connect' => false]);
```

### headers

While you can set any headers this request needs by using the `setHeader()` method, you can also pass an associative array of headers in as an option. Each key is the name of a header, and each value is a string or array of strings representing the header field values:

```php
<?php

$client->request('GET', '/', [
    'headers' => [
        'User-Agent' => 'testing/1.0',
        'Accept'     => 'application/json',
        'X-Foo'      => ['Bar', 'Baz'],
    ],
]);
```

If headers are passed into the constructor, they are treated as default values that will be overridden later by any further headers arrays or calls to `setHeader()`.

### http_errors

By default, CURLRequest will throw `HTTPException` if the HTTP code returned is greater than or equal to 400.

If you want to see the response body, you can set `http_errors` to `false` to return the content instead:

```php
<?php

$client->request('GET', '/status/500');
// If the response code is 500, an HTTPException is thrown,
// and a detailed error report is displayed if in development mode.

$response = $client->request('GET', '/status/500', ['http_errors' => false]);
echo $response->getStatusCode(); // 500
echo $response->getBody();       // You can see the response body.
```

### json

The `json` option is used to easily upload JSON-encoded data as the body of a request. A Content-Type header of `application/json` is added, overwriting any Content-Type that might already be set. The data provided to this option can be any value that `json_encode()` accepts:

```php
<?php

$response = $client->request('PUT', '/put', ['json' => ['foo' => 'bar']]);
```

!!! note "Note"
    This option does not allow for any customization of the `json_encode()` function, or the Content-Type header. If you need that ability, you will need to encode the data manually, pass it through the `setBody()` method of CURLRequest, and set the Content-Type header with the `setHeader()` method.

### multipart

When you need to send files and other data via a POST request, you can use the `multipart` option, along with the [CURLFile Class](https://www.php.net/manual/en/class.curlfile.php).

The values should be an associative array of POST data to send. For safer usage, the legacy method of uploading files by prefixing their name with an `@` has been disabled. Any files that you want to send must be passed as instances of CURLFile:

```php
<?php

$client->request('POST', '/post', [
    'multipart' => [
        'foo'      => 'bar',
        'userfile' => new \CURLFile('/path/to/file.txt'),
    ],
]);
```

!!! note "Note"
    `multipart` cannot be used with the [form_params](#form_params) option. You can only use one or the other. Use `form_params` for `application/x-www-form-urlencoded` requests, and `multipart` for `multipart/form-data` requests.

### proxy

!!! success "Available from version 4.4.0"

You can set a proxy by passing an associative array as the `proxy` option:

```php
<?php

$client->request(
    'GET',
    'http://example.com',
    ['proxy' => 'http://localhost:3128'],
);
```

### query

You can pass along data to send as query string variables by passing an associative array as the `query` option:

```php
<?php

// Send a GET request to /get?foo=bar
$client->request('GET', '/get', ['query' => ['foo' => 'bar']]);
```

### timeout

By default, cURL functions are allowed to run as long as they take, with no time limit. You can modify this with the `timeout` option. The value should be the number of seconds you want the functions to execute for. Use 0 to wait indefinitely:

```php
<?php

$client->request('GET', 'http://example.com', ['timeout' => 5]);
```

### user_agent

Allows specifying the User Agent for requests:

```php
<?php

$client->request('GET', 'http://example.com', ['user_agent' => 'CodeIgniter Framework v4']);
```

### verify

This option describes the SSL certificate verification behavior. If the `verify` option is `true`, it enables the SSL certificate verification and uses the default CA bundle provided by the operating system. If set to `false`, it will disable the certificate verification (this is insecure, and allows man-in-the-middle attacks!). You can set it to a string that contains the path to a CA bundle to enable verification with a custom certificate. The default value is true:

```php
<?php

// Use the system's CA bundle (this is the default setting)
$client->request('GET', '/', ['verify' => true]);

// Use a custom SSL certificate on disk.
$client->request('GET', '/', ['verify' => '/path/to/cert.pem']);

// Disable validation entirely. (Insecure!)
$client->request('GET', '/', ['verify' => false]);
```

### force_ip_resolve

!!! success "Available from version 4.6.0"

To set the HTTP handlers to use only the IPv4 protocol (`v4`) or the IPv6 protocol (`v6`):

```php
<?php

// Force ipv4 resolve
$client->request('GET', '/', ['force_ip_resolve' => 'v4']); // v4 or v6
```

### version

To set the HTTP protocol to use, you can pass a string or float with the version number (typically either `1.0` or `1.1`, with `2.0` supported since v4.3.0):

```php
<?php

// Force HTTP/1.0
$client->request('GET', '/', ['version' => 1.0]);
```
