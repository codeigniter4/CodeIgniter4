# Cookies

An **HTTP cookie** (web cookie, browser cookie) is a small piece of data that a server sends to the user's web browser. The browser may store it and send it back with later requests to the same server. Typically, it's used to tell if two requests came from the same browser - keeping a user logged in, for example. It remembers stateful information for the stateless HTTP protocol.

Cookies are mainly used for three purposes:

- **Session management**: Logins, shopping carts, game scores, or anything else the server should remember
- **Personalization**: User preferences, themes, and other settings
- **Tracking**: Recording and analyzing user behavior

To help you efficiently send cookies to browsers, CodeIgniter provides the `CodeIgniter\Cookie\Cookie` class to abstract the cookie interaction.

## Creating Cookies

There are currently four ways to create a new `Cookie` value object.

```php
<?php

use CodeIgniter\Cookie\Cookie;
use DateTime;

// Using the constructor
$cookie = new Cookie(
    'remember_token',
    'f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6',
    [
        'expires'  => new DateTime('+2 hours'),
        'prefix'   => '__Secure-',
        'path'     => '/',
        'domain'   => '',
        'secure'   => true,
        'httponly' => true,
        'raw'      => false,
        'samesite' => Cookie::SAMESITE_LAX,
    ],
);

// Supplying a Set-Cookie header string
$cookie = Cookie::fromHeaderString(
    'remember_token=f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6; Path=/; Secure; HttpOnly; SameSite=Lax',
    false, // raw
);

// Using the fluent builder interface
$cookie = (new Cookie('remember_token'))
    ->withValue('f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6')
    ->withPrefix('__Secure-')
    ->withExpires(new DateTime('+2 hours'))
    ->withPath('/')
    ->withDomain('')
    ->withSecure(true)
    ->withHTTPOnly(true)
    ->withSameSite(Cookie::SAMESITE_LAX);

// Using the global function `cookie` which implicitly calls `new Cookie()`
$cookie = cookie('remember_token', 'f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6');
```

When constructing the `Cookie` object, only the `name` attribute is required. All others are optional. If the optional attributes are not modified, their values will be filled with the default values saved in the `Cookie` class.

### Overriding Defaults

To override the defaults currently stored in the class, you can pass a `Config\Cookie` instance or an array of defaults to the static `Cookie::setDefaults()` method.

```php
<?php

use CodeIgniter\Cookie\Cookie;
use Config\Cookie as CookieConfig;

// pass in a Config\Cookie instance before constructing a Cookie class
Cookie::setDefaults(config(CookieConfig::class));
$cookie = new Cookie('login_token');

// pass in an array of defaults
$myDefaults = [
    'expires'  => 0,
    'samesite' => Cookie::SAMESITE_STRICT,
];
Cookie::setDefaults($myDefaults);
$cookie = new Cookie('login_token');
```

Passing the `Config\Cookie` instance or an array to `Cookie::setDefaults()` will effectively overwrite your defaults and will persist until new defaults are passed.

#### Changing Defaults for a Limited Time

If you do not want this behavior but only want to change defaults for a limited time, you can take advantage of the return value of `Cookie::setDefaults()`, which is the old defaults array.

```php
<?php

use CodeIgniter\Cookie\Cookie;
use Config\Cookie as CookieConfig;

$oldDefaults = Cookie::setDefaults(config(CookieConfig::class));
$cookie      = new Cookie('my_token', 'muffins');

// return the old defaults
Cookie::setDefaults($oldDefaults);
```

## Accessing Cookie's Attributes

Once instantiated, you can easily access a `Cookie`'s attribute by using one of its getter methods.

```php
<?php

use CodeIgniter\Cookie\Cookie;
use DateTime;
use DateTimeZone;

$cookie = new Cookie(
    'remember_token',
    'f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6',
    [
        'expires'  => new DateTime('2025-02-14 00:00:00', new DateTimeZone('UTC')),
        'prefix'   => '__Secure-',
        'path'     => '/',
        'domain'   => '',
        'secure'   => true,
        'httponly' => true,
        'raw'      => false,
        'samesite' => Cookie::SAMESITE_LAX,
    ],
);

$cookie->getName();             // 'remember_token'
$cookie->getPrefix();           // '__Secure-'
$cookie->getPrefixedName();     // '__Secure-remember_token'
$cookie->getExpiresTimestamp(); // UNIX timestamp
$cookie->getExpiresString();    // 'Fri, 14 Feb 2025 00:00:00 GMT'
$cookie->isExpired();           // false
$cookie->getMaxAge();           // the difference from time() to expires
$cookie->isRaw();               // false
$cookie->isSecure();            // true
$cookie->getPath();             // '/'
$cookie->getDomain();           // ''
$cookie->isHTTPOnly();          // true
$cookie->getSameSite();         // 'Lax'

// additional getter
$cookie->getId(); // '__Secure-remember_token;;/'

// when using `setcookie()`'s alternative signature on PHP 7.3+
// you can easily use the `getOptions()` method to supply the
// $options parameter
$cookie->getOptions();
```

## Immutable Cookies

A new `Cookie` instance is an immutable value object representation of an HTTP cookie. Since it is immutable, modifying any of the instance's attributes will not affect the original instance. The modification **always** returns a new instance. You need to retain this new instance in order to use it.

```php
<?php

use CodeIgniter\Cookie\Cookie;

$cookie = new Cookie('login_token', 'admin');
$cookie->getName(); // 'login_token'

$cookie->withName('remember_token');
$cookie->getName(); // 'login_token'

$new = $cookie->withName('remember_token');
$new->getName(); // 'remember_token'
```

## Validating a Cookie's Attributes

An HTTP cookie is regulated by several specifications that need to be followed in order to be accepted by browsers. Thus, when creating or modifying certain attributes of the `Cookie`, these are validated in order to check if they follow the specifications.

A `CookieException` is thrown if violations were reported.

### Validating the Name Attribute

A cookie name can be any US-ASCII character, except for the following:

- control characters;
- spaces or tabs;
- separator characters, such as `( ) < > @ , ; : \ " / [ ] ? = { }`

If the `$raw` parameter is set to `true`, this validation will be strictly enforced. This is because PHP's [setcookie()](https://www.php.net/manual/en/function.setcookie.php) and [setrawcookie()](https://www.php.net/manual/en/function.setrawcookie.php) will reject cookies with invalid names. Additionally, cookie names cannot be an empty string.

### Validating the Value Attribute

If setting the `$raw` parameter to `true`, the cookie value will also be validated. It must not contain control characters, spaces, tabs, or separator characters (`, ;`) as [setrawcookie()](https://www.php.net/manual/en/function.setrawcookie.php) will reject them.

### Validating the Path Attribute

The cookie path must not contain control characters, spaces, tabs, or separator characters (`, ;`) as [setcookie()](https://www.php.net/manual/en/function.setcookie.php) and [setrawcookie()](https://www.php.net/manual/en/function.setrawcookie.php) will reject them.

### Validating the Domain Attribute

The cookie domain must not contain control characters, spaces, tabs, or separator characters (`, ;`) as [setcookie()](https://www.php.net/manual/en/function.setcookie.php) and [setrawcookie()](https://www.php.net/manual/en/function.setrawcookie.php) will reject them.

### Validating the Prefix Attribute

The cookie prefix must not contain control characters, spaces, tabs, or separator characters (`= , ; \t \r \n \v \f \0`) as [setcookie()](https://www.php.net/manual/en/function.setcookie.php) and [setrawcookie()](https://www.php.net/manual/en/function.setrawcookie.php) will reject them.

When using the `__Secure-` prefix, cookies must be set with the `$secure` flag set to `true`. If using the `__Host-` prefix, cookies must exhibit the following:

- `$secure` flag set to `true`
- `$domain` is empty
- `$path` must be `/`

### Validating the SameSite Attribute

The SameSite attribute accepts three values:

- **Lax**: Cookies are not sent on normal cross-site subrequests (for example, to load images or frames into a third-party site), but are sent when a user is navigating to the origin site (*i.e.* when following a link).
- **Strict**: Cookies will only be sent in a first-party context and not be sent along with requests initiated by third-party websites.
- **None**: Cookies will be sent in all contexts, *i.e.* in responses to both first-party and cross-origin requests.

CodeIgniter, however, allows you to set the SameSite attribute to an empty string. When an empty string is provided, the default SameSite setting saved in the `Cookie` class is used. You can change the default SameSite by using `Cookie::setDefaults()` as discussed above.

Recent cookie specifications have changed such that modern browsers are required to give a default SameSite if nothing is provided. This default is `Lax`. If you have set the SameSite to be an empty string and your default SameSite is also an empty string, your cookie will be given the `Lax` value.

If the SameSite is set to `None`, you need to make sure that `Secure` is also set to `true`.

When writing the SameSite attribute, the `Cookie` class accepts any of the values case-insensitively. You can also take advantage of the class's constants to make this easier.

```php
<?php

use CodeIgniter\Cookie\Cookie;

Cookie::SAMESITE_LAX;    // 'Lax'
Cookie::SAMESITE_STRICT; // 'Strict'
Cookie::SAMESITE_NONE;   // 'None'
```

## Sending Cookies

Set the `Cookie` objects in the `CookieStore` of the Response object, and the framework will automatically send the cookies.

Use `CodeIgniter\HTTP\Response::setCookie()` to set:

```php
<?php

use CodeIgniter\Cookie\Cookie;

$response = service('response');

$cookie = new Cookie(
    'remember_token',
    'f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6',
    [
        'max-age' => 3600 * 2, // Expires in 2 hours
    ],
);

$response->setCookie($cookie);
```

You can also use the `set_cookie()` helper function:

```php
<?php

use CodeIgniter\Cookie\Cookie;

helper('cookie');

$cookie = new Cookie(
    'remember_token',
    'f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6',
    [
        'max-age' => 3600 * 2, // Expires in 2 hours
    ],
);

set_cookie($cookie);
```

## Using the Cookie Store

!!! note "Note"
    Normally, there is no need to use CookieStore directly.

The `CookieStore` class represents an immutable collection of `Cookie` objects.

### Getting the Store from Response

The `CookieStore` instance can be accessed from the current `Response` object.

```php
<?php

$cookieStore = service('response')->getCookieStore();
```

### Creating CookieStore

CodeIgniter provides three other ways to create a new instance of the `CookieStore`.

```php
<?php

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\Cookie\CookieStore;

// Passing an array of `Cookie` objects in the constructor
$store = new CookieStore([
    new Cookie('login_token'),
    new Cookie('remember_token'),
]);

// Passing an array of `Set-Cookie` header strings
$store = CookieStore::fromCookieHeaders([
    'remember_token=me; Path=/; SameSite=Lax',
    'login_token=admin; Path=/; SameSite=Lax',
]);

// using the global `cookies` function
$store = cookies([new Cookie('login_token')], false);

// retrieving the `CookieStore` instance saved in our current `Response` object
$store = cookies();
```

!!! note "Note"
    When using the global `cookies()` function, the passed `Cookie` array will only be considered if the second argument, `$getGlobal`, is set to `false`.

### Checking Cookies in Store

There are several ways to check whether a `Cookie` object exists in the `CookieStore` instance:

```php
<?php

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\Cookie\CookieStore;

// check if cookie is in the current cookie collection
$store = new CookieStore([
    new Cookie('login_token'),
    new Cookie('remember_token'),
]);
$store->has('login_token');

// check if cookie is in the current Response's cookie collection
cookies()->has('login_token');
service('response')->hasCookie('remember_token');

// using the cookie helper to check the current Response
// not available to v4.1.1 and lower
helper('cookie');
has_cookie('login_token');
```

### Getting Cookies in Store

Retrieving a `Cookie` instance in a cookie collection is very easy:

```php
<?php

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\Cookie\CookieStore;
use Config\Services;

// getting cookie in the current cookie collection
$store = new CookieStore([
    new Cookie('login_token'),
    new Cookie('remember_token'),
]);
$store->get('login_token');

// getting cookie in the current Response's cookie collection
cookies()->get('login_token');
service('response')->getCookie('remember_token');

// using the cookie helper to get cookie from the Response's cookie collection
helper('cookie');
get_cookie('remember_token');
```

When getting a `Cookie` instance directly from a `CookieStore`, an invalid name will throw a `CookieException`.

```php
<?php

// throws CookieException
$store->get('unknown_cookie');
```

When getting a `Cookie` instance from the current `Response`'s cookie collection, an invalid name will just return `null`.

```php
<?php

cookies()->get('unknown_cookie'); // null
```

If no arguments are supplied when getting cookies from the `Response`, all `Cookie` objects in store will be displayed.

```php
<?php

cookies()->display(); // array of Cookie objects

// or even from the Response
service('response')->getCookies();
```

!!! note "Note"
    The helper function `get_cookie()` gets the cookie from the current `Request` object, not from `Response`. This function checks the `$_COOKIE` array to see if that cookie is set and fetches it right away.

### Adding/Removing Cookies in Store

As previously mentioned, `CookieStore` objects are immutable. You need to save the modified instance in order to work on it. The original instance is left unchanged.

```php
<?php

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\Cookie\CookieStore;

$store = new CookieStore([
    new Cookie('login_token'),
    new Cookie('remember_token'),
]);

// adding a new Cookie instance
$new = $store->put(new Cookie('admin_token', 'yes'));

// removing a Cookie instance
$new = $store->remove('login_token');
```

!!! note "Note"
    Removing a cookie from the store **DOES NOT** delete it from the browser. If you intend to delete a cookie *from the browser*, you must put an empty-value cookie with the same name into the store.

When interacting with the cookies in store in the current `Response` object, you can safely add or delete cookies without worrying about the immutable nature of the cookie collection. The `Response` object will replace the instance with the modified instance.

```php
<?php

service('response')->setCookie('admin_token', 'yes');
service('response')->deleteCookie('login_token');

// using the cookie helper
helper('cookie');
set_cookie('admin_token', 'yes');
delete_cookie('login_token');
```

### Dispatching Cookies in Store

!!! important "Important"
    This method was deprecated as of version 4.1.6 and was removed in 4.6.0.

More often than not, you do not need to concern yourself with manually sending cookies. CodeIgniter will do this for you. However, if you really need to manually send cookies, you can use the `dispatch` method. Just like when sending other headers, you need to make sure the headers are not yet sent by checking the value of `headers_sent()`.

```php
<?php

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\Cookie\CookieStore;

$store = new CookieStore([
    new Cookie('login_token'),
    new Cookie('remember_token'),
]);

$store->dispatch(); // After dispatch, the collection is now empty.
```

## Cookie Personalization

Sane defaults are already in place inside the `Cookie` class to ensure the smooth creation of cookie objects. However, you may wish to define your own settings by changing the following settings in the `Config\Cookie` class in the **app/Config/Cookie.php** file.

| Setting | Options/Types | Default | Description |
|----|----|----|----|
| **$prefix** | `string` | `''` | Prefix to prepend to the cookie name. |
| **$expires** | `DateTimeInterface|string|int` | `0` | The expires timestamp. |
| **$path** | `string` | `/` | The path property of the cookie. |
| **$domain** | `string` | `''` | The domain property of the cookie. |
| **$secure** | `true`/`false` | `false` | Whether to send it over secure HTTPS. |
| **$httponly** | `true`/`false` | `true` | Whether it is inaccessible to JavaScript. |
| **$samesite** | `Lax`/`None`/`Strict` | `Lax` | The SameSite attribute. |
| **$raw** | `true`/`false` | `false` | Whether to dispatch it using `setrawcookie()`. |

At runtime, you can manually supply a new default using the `Cookie::setDefaults()` method.

## Class Reference

## CodeIgniter\Cookie

### Cookie

#### setDefaults([$config = []])

- **Parameters**  
    - \Config\Cookie|array `$config` The configuration array or instance

- **Return type**: `array<string, mixed>`

- **Returns**: The old defaults

Set the default attributes to a Cookie instance by injecting the values from the `Config\Cookie` config or an array.

#### fromHeaderString(string $header[, bool $raw = false])

- **Parameters**  
    - **$header** `string` The `Set-Cookie` header string
    - **$raw** `bool` Whether this cookie is not to be URL encoded and sent via `setrawcookie()`

- **Return type**: `Cookie`

- **Returns**: `Cookie` instance

- **Throws**: `CookieException`

Create a new Cookie instance from a `Set-Cookie` header.

#### \__construct(string $name[, string $value = ''[, array $options = []]])

- **Parameters**  
    - **$name** `string` The cookie name
    - **$value** `string` The cookie value
    - **$options** `array` The cookie options

- **Return type**: `Cookie`

- **Returns**: `Cookie` instance

- **Throws**: `CookieException`

Construct a new Cookie instance.

#### getId()

- **Return type**: `string`
- **Returns**: The ID used in indexing in the cookie collection.

#### getPrefix(): string

#### getName(): string

#### getPrefixedName(): string

#### getValue(): string

#### getExpiresTimestamp(): int

#### getExpiresString(): string

#### isExpired(): bool

#### getMaxAge(): int

#### getDomain(): string

#### getPath(): string

#### isSecure(): bool

#### isHTTPOnly(): bool

#### getSameSite(): string

#### isRaw(): bool

#### getOptions(): array

#### withRaw([bool $raw = true])

- **Parameters**  
    - **$raw** `bool`

- **Return type**: `Cookie`

- **Returns**: new `Cookie` instance

Creates a new Cookie with the URL encoding option updated.

#### withPrefix([string $prefix = ''])

- **Parameters**  
    - **$prefix** `string`

- **Return type**: `Cookie`

- **Returns**: new `Cookie` instance

Creates a new Cookie with a new prefix.

#### withName(string $name)

- **Parameters**  
    - **$name** `string`

- **Return type**: `Cookie`

- **Returns**: new `Cookie` instance

Creates a new Cookie with a new name.

#### withValue(string $value)

- **Parameters**  
    - **$value** `string`

- **Return type**: `Cookie`

- **Returns**: new `Cookie` instance

Creates a new Cookie with a new value.

#### withExpires($expires)

- **Parameters**  
    - **$expires** `DateTimeInterface|string|int`

- **Return type**: `Cookie`

- **Returns**: new `Cookie` instance

Creates a new Cookie with a new cookie expires time.

#### withExpired()

- **Return type**: `Cookie`
- **Returns**: new `Cookie` instance

Creates a new Cookie that will expire from the browser.

#### withNeverExpiring()

!!! important "Important"
    This method was deprecated as of version 4.2.6 and was removed in 4.6.0.

- **Return type**: `Cookie`
- **Returns**: new `Cookie` instance

Creates a new Cookie that will virtually never expire.

#### withDomain(?string $domain)

- **Parameters**  
    - **$domain** `string|null`

- **Return type**: `Cookie`

- **Returns**: new `Cookie` instance

Creates a new Cookie with a new domain.

#### withPath(?string $path)

- **Parameters**  
    - **$path** `string|null`

- **Return type**: `Cookie`

- **Returns**: new `Cookie` instance

Creates a new Cookie with a new path.

#### withSecure([bool $secure = true])

- **Parameters**  
    - **$secure** `bool`

- **Return type**: `Cookie`

- **Returns**: new `Cookie` instance

Creates a new Cookie with a new "Secure" attribute.

#### withHTTPOnly([bool $httponly = true])

- **Parameters**  
    - **$httponly** `bool`

- **Return type**: `Cookie`

- **Returns**: new `Cookie` instance

Creates a new Cookie with a new "HttpOnly" attribute.

#### withSameSite(string $samesite)

- **Parameters**  
    - **$samesite** `string`

- **Return type**: `Cookie`

- **Returns**: new `Cookie` instance

Creates a new Cookie with a new "SameSite" attribute.

#### toHeaderString()

- **Return type**: `string`
- **Returns**: Returns the string representation that can be passed as a header string.

#### toArray()

- **Return type**: `array`
- **Returns**: Returns the array representation of the Cookie instance.

### CookieStore

#### fromCookieHeaders(array $headers[, bool $raw = false])

- **Parameters**  
    - **$headers** `array` Array of `Set-Cookie` headers
    - **$raw** `bool` Whether not to use URL encoding

- **Return type**: `CookieStore`

- **Returns**: `CookieStore` instance

- **Throws**: `CookieException`

Creates a CookieStore from an array of `Set-Cookie` headers.

#### \__construct(array $cookies)

- **Parameters**  
    - **$cookies** `array` Array of `Cookie` objects

- **Return type**: `CookieStore`

- **Returns**: `CookieStore` instance

- **Throws**: `CookieException`

#### has(string $name[, string $prefix = ''[, ?string $value = null]]): bool

- **Parameters**  
    - **$name** `string` Cookie name
    - **$prefix** `string` Cookie prefix
    - **$value** `string|null` Cookie value

- **Return type**: `bool`

- **Returns**: Checks if a `Cookie` object identified by name and prefix is present in the collection.

#### get(string $name[, string $prefix = '']): Cookie

- **Parameters**  
    - **$name** `string` Cookie name
    - **$prefix** `string` Cookie prefix

- **Return type**: `Cookie`

- **Returns**: Retrieves an instance of Cookie identified by a name and prefix.

- **Throws**: `CookieException`

#### put(Cookie $cookie): CookieStore

- **Parameters**  
    - **$cookie** `Cookie` A Cookie object

- **Return type**: `CookieStore`

- **Returns**: new `CookieStore` instance

Store a new cookie and return a new collection. The original collection is left unchanged.

#### remove(string $name[, string $prefix = '']): CookieStore

- **Parameters**  
    - **$name** `string` Cookie name
    - **$prefix** `string` Cookie prefix

- **Return type**: `CookieStore`

- **Returns**: new `CookieStore` instance

Removes a cookie from a collection and returns an updated collection. The original collection is left unchanged.

#### dispatch(): void

!!! important "Important"
    This method was deprecated as of version 4.1.6 and was removed in 4.6.0.

- **Return type**: `void`

Dispatches all cookies in store.

#### display(): array

- **Return type**: `array`
- **Returns**: Returns all cookie instances in store.

#### clear(): void

- **Return type**: `void`

Clears the cookie collection.
