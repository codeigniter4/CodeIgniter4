# Working with URIs

CodeIgniter provides an object-oriented solution for working with URIs in your application. Using this makes it simple to ensure that the structure is always correct, no matter how complex the URI might be, as well as to add a relative URI to an existing one and have it resolved safely and correctly.

## Creating URI instances

Creating a URI instance is as simple as creating a new class instance.

When you create the new instance, you can pass a full or partial URL in the constructor and it will be parsed into its appropriate sections:

```php
$uri = new \CodeIgniter\HTTP\URI('http://www.example.com/some/path');
```

Alternatively, you can use the `service()` function to return an instance for you:

```php
$uri = service('uri', 'http://www.example.com/some/path');
```

Since v4.4.0, if you don't pass a URL, it returns the current URI:

```php
$uri = service('uri'); // returns the current SiteURI instance.
```

!!! note "Note"
    The above code returns a `SiteURI` instance, which extends the `URI` class. The `URI` class is for general URIs, but the `SiteURI` class is for your site URIs.

### The Current URI

When you need an object representing the current URL of the request, you can use the `current_url()` function available in the [URL Helper](../helpers/url_helper.md):

```php
$uri = current_url(true);
```

You must pass `true` as the first parameter. Otherwise, it will return the string representation of the current URL.

This URI is based on the path (relative to your `baseURL`) as determined by the current request object and your settings in `Config\App` (`baseURL`, `indexPage`, and `forceGlobalSecureRequests`).

Assuming that you're in a controller that extends `CodeIgniter\Controller`, you can also get the current SiteURI instance:

```php
$uri = $this->request->getUri();
```

## URI Strings

Many times, all you really want is to get a string representation of a URI. This is easy to do by simply casting the URI as a string:

```php
<?php

$uri = current_url(true);
echo (string) $uri;  // http://example.com/index.php
```

If you know the pieces of the URI and just want to ensure it's all formatted correctly, you can generate a string using the URI class' static `createURIString()` method:

```php
<?php

use CodeIgniter\HTTP\URI;

$uriString = URI::createURIString($scheme, $authority, $path, $query, $fragment);

// Creates: http://exmample.com/some/path?foo=bar#first-heading
echo URI::createURIString('http', 'example.com', 'some/path', 'foo=bar', 'first-heading');
```

!!! important "Important"
    When `URI` is cast to a string, it will attempt to adjust project URLs to the settings defined in `Config\App`. If you need the exact, unaltered string representation then use `URI::createURIString()` instead.

## The URI Parts

Once you have a URI instance, you can set or retrieve any of the various parts of the URI. This section will provide details on what those parts are, and how to work with them.

### Scheme

The scheme is frequently 'http' or 'https', but any scheme is supported, including 'file', 'mailto', etc.

```php
<?php

$uri = new \CodeIgniter\HTTP\URI('http://www.example.com/some/path');

echo $uri->getScheme(); // 'http'
$uri->setScheme('https');
```

### Authority

Many URIs contain several elements that are collectively known as the 'authority'. This includes any user info, the host and the port number. You can retrieve all of these pieces as one single string with the `getAuthority()` method, or you can manipulate the individual parts.

```php
<?php

$uri = new \CodeIgniter\HTTP\URI('ftp://user:password@example.com:21/some/path');

echo $uri->getAuthority();  // user@example.com:21
```

By default, this will not display the password portion since you wouldn't want to show that to anyone. If you want to show the password, you can use the `showPassword()` method. This URI instance will continue to show that password until you turn it off again, so always make sure that you turn it off as soon as you are finished with it:

```php
<?php

echo $uri->getAuthority();  // user@example.com:21
echo $uri->showPassword()->getAuthority();   // user:password@example.com:21

// Turn password display off again.
$uri->showPassword(false);
```

If you do not want to display the port, pass in `true` as the only parameter:

```php
<?php

echo $uri->getAuthority(true);  // user@example.com
```

!!! note "Note"
    If the current port is the default port for the scheme, it will never be displayed.

### UserInfo

The userinfo section is simply the username and password that you might see with an FTP URI. While you can get this as part of the Authority, you can also retrieve it yourself:

```php
<?php

echo $uri->getUserInfo();   // user
```

By default, it will not display the password, but you can override that with the `showPassword()` method:

```php
<?php

echo $uri->showPassword()->getUserInfo();   // user:password
$uri->showPassword(false);
```

### Host

The host portion of the URI is typically the domain name of the URL. This can be easily set and retrieved with the `getHost()` and `setHost()` methods:

```php
<?php

$uri = new \CodeIgniter\HTTP\URI('http://www.example.com/some/path');

echo $uri->getHost();   // www.example.com
echo $uri->setHost('anotherexample.com')->getHost();    // anotherexample.com
```

### Port

The port is an integer between 0 and 65535. Each scheme has a default value associated with it.

```php
<?php

$uri = new \CodeIgniter\HTTP\URI('ftp://user:password@example.com:21/some/path');

echo $uri->getPort();   // 21
echo $uri->setPort(2201)->getPort(); // 2201
```

When using the `setPort()` method, the port will be checked to ensure that it is within the valid range, and then assigned.

### Path

The path is all of the segments within the site itself. As expected, the `getPath()` and `setPath()` methods can be used to manipulate it:

```php
<?php

$uri = new \CodeIgniter\HTTP\URI('http://www.example.com/some/path');

echo $uri->getPath();                            // '/some/path'
echo $uri->setPath('/another/path')->getPath();  // '/another/path'
```

!!! note "Note"
    When the path is set, it is sanitized to encode any dangerous characters and remove dot segments for safety.

!!! note "Note"
    Since v4.4.0, the `SiteURI::getRoutePath()` method returns the URI path relative to baseURL, and the `SiteURI::getPath()` method always returns the full URI path with leading `/`.

### Query

The query data can be manipulated through the class using simple string representations.

#### Getting/Setting Query

Query values can only be set as a string currently.

```php
<?php

$uri = new \CodeIgniter\HTTP\URI('http://www.example.com?foo=bar');

echo $uri->getQuery();  // 'foo=bar'
$uri->setQuery('foo=bar&bar=baz');
```

The `setQuery()` method overwrites existing query variables.

!!! note "Note"
    Query values cannot contain fragments. An InvalidArgumentException will be thrown if they do.

#### Setting Query from Array

You can set query values using an array:

```php
<?php

$uri->setQueryArray(['foo' => 'bar', 'bar' => 'baz']);
```

The `setQueryArray()` method overwrites existing query variables.

#### Adding Query Value

You can add a value to the query variables collection without destroying the existing query variables with the `addQuery()` method. The first parameter is the name of the variable, and the second parameter is the value:

```php
<?php

$uri->addQuery('foo', 'bar');
```

#### Filtering Query Values

You can filter the query values returned by passing an options array to the `getQuery()` method, with either an *only* or an *except* key:

```php
<?php

$uri = new \CodeIgniter\HTTP\URI('http://www.example.com?foo=bar&bar=baz&baz=foz');

// Returns 'foo=bar'
echo $uri->getQuery(['only' => ['foo']]);

// Returns 'foo=bar&baz=foz'
echo $uri->getQuery(['except' => ['bar']]);
```

This only changes the values returned during this one call.

#### Changing Query Values

If you need to modify the URI's query values more permanently, you can use the `stripQuery()` and `keepQuery()` methods to change the actual object's query variable collection:

```php
<?php

$uri = new \CodeIgniter\HTTP\URI('http://www.example.com?foo=bar&bar=baz&baz=foz');

// Leaves just the 'baz' variable
$uri->stripQuery('foo', 'bar');

// Leaves just the 'foo' variable
$uri->keepQuery('foo');
```

!!! note "Note"
    By default, the `setQuery()` and `setQueryArray()` methods use the native `parse_str()` function to prepare data. If you want to use more liberal rules (which allow key names to contain dots), you can use a special method `useRawQueryString()` beforehand.

### Fragment

Fragments are the portion at the end of the URL, preceded by the pound sign (`#`). In HTML URLs these are links to an on-page anchor. Media URIs can make use of them in various other ways.

```php
<?php

$uri = new \CodeIgniter\HTTP\URI('http://www.example.com/some/path#first-heading');

echo $uri->getFragment();   // 'first-heading'
echo $uri->setFragment('second-heading')->getFragment();    // 'second-heading'
```

## URI Segments

Each section of the path between the slashes is a single segment.

!!! note "Note"
    In the case of your site URI, URI Segments mean only the URI path part relative to the baseURL. If your baseURL contains subfolders, the values will be different from the current URI path.

The URI class provides a simple way to determine what the values of the segments are. The segments start at 1 being the furthest left of the path.

```php
<?php

// URI = http://example.com/users/15/profile

// Prints '15'
if ($uri->getSegment(1) === 'users') {
    echo $uri->getSegment(2);
}
```

You can also set a different default value for a particular segment by using the second parameter of the `getSegment()` method. The default is an empty string.

```php
<?php

// URI = http://example.com/users/15/profile

// will print 'profile'
echo $uri->getSegment(3, 'foo');
// will print 'bar'
echo $uri->getSegment(4, 'bar');
// will throw an exception
echo $uri->getSegment(5, 'baz');
// will print 'baz'
echo $uri->setSilent()->getSegment(5, 'baz');
// will print '' (empty string)
echo $uri->setSilent()->getSegment(5);
```

!!! note "Note"
    You can get the last +1 segment. When you try to get the last +2 or higher segment, an exception will be thrown by default. You can prevent exceptions from being thrown with the `setSilent()` method.

You can get a count of the total segments:

```php
<?php

$total = $uri->getTotalSegments(); // 3
```

Finally, you can retrieve an array of all of the segments:

```php
<?php

$segments = $uri->getSegments();

/*
 * Produces:
 * [
 *     0 => 'users',
 *     1 => '15',
 *     2 => 'profile',
 * ]
 */
```

## Disable Throwing Exceptions

By default, some methods of this class may throw an exception. If you want to disable it, you can set a special flag that will prevent throwing exceptions.

```php
<?php

// Disable throwing exceptions
$uri->setSilent();

// Enable throwing exceptions (default)
$uri->setSilent(false);
```
