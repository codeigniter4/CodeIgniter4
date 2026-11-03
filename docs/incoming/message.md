# HTTP Messages

The Message class provides an interface to the portions of an HTTP message that are common to both requests and responses, including the message body, protocol version, utilities for working with the headers, and methods for handling content negotiation.

This class is the parent class that both the [Request Class](../incoming/request.md) and the [Response Class](../outgoing/response.md) extend from, and it is not used directly.

## Class Reference

## CodeIgniter\HTTP

### Message

#### getBody()

- **Returns**: The current message body
- **Return type**: `mixed`

Returns the current message body, if any has been set. If no body exists, returns null:

```php
<?php

echo $message->getBody();
```

#### setBody($data)

- **Parameters**  
    - **$data** `mixed` The body of the message.

- **Returns**: The Message|Response instance to allow methods to be chained together.

- **Return type**: `CodeIgniter\HTTP\Message|CodeIgniter\HTTP\Response`

Sets the body of the current request.

#### appendBody($data)

- **Parameters**  
    - **$data** `mixed` The body of the message.

- **Returns**: The Message|Response instance to allow methods to be chained together.

- **Return type**: `CodeIgniter\HTTP\Message|CodeIgniter\HTTP\Response`

Appends data to the body of the current request.

#### populateHeaders()

- **Returns**: void

Scans and parses the headers found in the SERVER data and stores them for later access. This is used by the [IncomingRequest Class](../incoming/incomingrequest.md) to make the current request's headers available.

The headers are any SERVER data that starts with `HTTP_`, like `HTTP_HOST`. Each message is converted from its standard uppercase and underscore format to a ucwords and dash format. The preceding `HTTP_` is removed from the string. So `HTTP_ACCEPT_LANGUAGE` becomes `Accept-Language`.

#### headers()

- **Returns**: An array of all of the headers found.
- **Return type**: `array`

Returns an array of all headers found or previously set.

#### header($name)

- **Parameters**  
    - **$name** `string` The name of the header you want to retrieve the value of.

- **Returns**: Returns a single header object. If multiple headers with the same name exist, it will return an array of header objects.

- **Return type**: `\CodeIgniter\HTTP\Header|array`

Allows you to retrieve the current value of a single message header. `$name` is the case-insensitive header name. While the header is converted internally as described above, you can access the header with any type of case:

```php
<?php

// These are all the same:
$message->header('HOST');
$message->header('Host');
$message->header('host');
```

If the header has multiple values, `getValue()` will return an array of values. You can use the `getValueLine()` method to retrieve the values as a string:

```php
<?php

echo $message->header('Accept-Language');
/*
 * Outputs something like:
 * 'Accept-Language: en,en-US'
 */

echo $message->header('Accept-Language')->getValue();
/*
 * Outputs something like:
 * [
 *     'en',
 *     'en-US',
 * ]
 */

echo $message->header('Accept-Language')->getValueLine();
/*
 * Outputs something like:
 * en,en-US'
 */
```

You can filter the header by passing a filter value in as the second parameter:

```php
<?php

$message->header('Document-URI', FILTER_SANITIZE_URL);
```

#### hasHeader($name)

- **Parameters**  
    - **$name** `string` The name of the header to check.

- **Returns**: Returns true if it exists, false otherwise.

- **Return type**: `bool`

#### getHeaderLine($name)

- **Parameters**  
    - **$name** `string` The name of the header to retrieve.

- **Returns**: A string representing the header value.

- **Return type**: `string`

Returns the value(s) of the header as a string. This method allows you to easily get a string representation of the header values when the header has multiple values. The values are appropriately joined:

```php
<?php

echo $message->getHeaderLine('Accept-Language');
/*
 * Outputs:
 * 'en,en-US'
 */
```

#### setHeader($name, $value)

- **Parameters**  
    - **$name** `string` The name of the header to set the value for.
    - **$value** `mixed` The value to set the header to.

- **Returns**: The current Message|Response instance

- **Return type**: `CodeIgniter\HTTP\Message|CodeIgniter\HTTP\Response`

Sets the value of a single header. `$name` is the case-insensitive name of the header. If the header doesn't already exist in the collection, it will be created. The `$value` can be either a string or an array of strings:

```php
<?php

$message->setHeader('Host', 'codeigniter.com');
```

#### removeHeader($name)

- **Parameters**  
    - **$name** `string` The name of the header to remove.

- **Returns**: The current message instance

- **Return type**: `CodeIgniter\HTTP\Message`

Removes the header from the Message. `$name` is the case-insensitive name of the header:

```php
<?php

$message->removeHeader('Host');
```

#### appendHeader($name, $value)

- **Parameters**  
    - **$name** `string` The name of the header to modify
    - **$value** `string` The value to add to the header.

- **Returns**: The current message instance

- **Return type**: `CodeIgniter\HTTP\Message`

Adds a value to an existing header. The header must already be an array of values instead of a single string. If it is a string then a LogicException will be thrown.

```php
<?php

$message->appendHeader('Accept-Language', 'en-US; q=0.8');
```

#### prependHeader($name, $value)

- **Parameters**  
    - **$name** `string` The name of the header to modify
    - **$value** `string` The value to prepend to the header.

- **Returns**: The current message instance

- **Return type**: `CodeIgniter\HTTP\Message`

Prepends a value to an existing header. The header must already be an array of values instead of a single string. If it is a string then a LogicException will be thrown.

```php
<?php

$message->prependHeader('Accept-Language', 'en,');
```

#### addHeader($name, $value)

!!! success "Available from version 4.5.0"

- **Parameters**  
    - **$name** `string` The name of the header to add.
    - **$value** `string` The value of the header.

- **Returns**: The current message instance

- **Return type**: `CodeIgniter\HTTP\Message`

Adds a header (not a header value) with the same name. Use this only when you set multiple headers with the same name:

```php
<?php

$message->addHeader('Set-Cookie', 'logged_in=no; Path=/');
$message->addHeader('Set-Cookie', 'sessid=123456; Path=/');
```

#### getProtocolVersion()

- **Returns**: The current HTTP protocol version
- **Return type**: `string`

Returns the message's current HTTP protocol. If none has been set, it will return `1.1`.

#### setProtocolVersion($version)

- **Parameters**  
    - **$version** `string` The HTTP protocol version

- **Returns**: The current message instance

- **Return type**: `CodeIgniter\HTTP\Message`

Sets the HTTP protocol version this Message uses. Valid values are `1.0`, `1.1`, `2.0` and `3.0`:

```php
<?php

$message->setProtocolVersion('1.1');
```
