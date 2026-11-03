# Context

!!! success "Available from version 4.8.0"

## What is it?

The Context class provides a simple, convenient way to store and retrieve user-defined data throughout a single request. It functions as a key-value store that can hold any data you need to access across different parts of your application during the request lifecycle.

The Context class is particularly useful for:

- Storing request-specific metadata (user IDs, request IDs, correlation IDs)
- Passing data between filters, controllers, and other components
- Adding contextual information to your logs automatically
- Storing sensitive data that should not appear in logs

## Accessing Context Class

You can access the Context service anywhere in your application using the `service()` function or the `context()` helper:

```php
<?php

$context = service('context');

// Or
$context = context();
```

## Setting Context Data

### Setting a Single Value

You can store a single key-value pair using the `set()` method:

```php
<?php

$context->set('user_id', 123);
```

### Setting Multiple Values

You can also set multiple values at once by passing an array:

```php
<?php

$context->set([
    'user_id'        => 123,
    'username'       => 'john_doe',
    'request_id'     => 'req_abc123',
    'correlation_id' => 'corr_xyz789',
]);
```

The `set()` method returns the Context instance, allowing you to chain multiple calls:

```php
<?php

$context->set('user_id', 123)
    ->set('username', 'john_doe')
    ->set('request_id', 'req_abc123');
```

## Getting Context Data

### Retrieving a Single Value

Use the `get()` method to retrieve a value by its key:

```php
<?php

$userId = $context->get('user_id');
// $userId = 123
```

You can provide a default value as the second parameter, which will be returned if the key doesn't exist:

```php
<?php

$role = $context->get('user_role', 'guest');
// If 'user_role' doesn't exist, $role will be 'guest'
```

### Retrieving All Data

To get all stored context data:

```php
<?php

$allData = $context->getAll();
// Returns: ['user_id' => 123, 'username' => 'john_doe', ...]
```

### Retrieving Specific Keys

You can retrieve only specific keys using `getOnly()`:

```php
<?php

$userData = $context->getOnly(['user_id', 'username']);
// Returns: ['user_id' => 123, 'username' => 'john_doe']

// You can also pass a single key as a string
$userId = $context->getOnly('user_id');
// Returns: ['user_id' => 123]
```

If you need all data except specific keys, use `getExcept()`:

```php
<?php

$data = $context->getExcept(['password', 'api_key']);
// Returns all data except 'password' and 'api_key'

// You can also pass a single key as a string
$data = $context->getExcept('password');
// Returns all data except 'password'
```

## Checking for Data

You can check if a key exists in the context:

```php
<?php

if ($context->has('user_id')) {
    // Do something with user_id
}
```

## Removing Context Data

### Removing a Single Value

You can remove data from the context using the `remove()` method:

```php
<?php

$context->remove('user_id');
```

### Removing Multiple Values

To remove multiple keys at once, pass an array:

```php
<?php

$context->remove(['user_id', 'username', 'request_id']);
```

### Clearing All Data

To remove all context data:

```php
<?php

$context->clear();
```

## Hidden Context Data

The Context class provides a separate storage area for sensitive data that should not be included in logs. This is useful for storing API keys, passwords, tokens, or other sensitive information that you need to access during the request but don't want to expose in log files.

### Setting Hidden Data

Use the `setHidden()` method to store sensitive data:

```php
<?php

$context->setHidden('api_key', 'sk_live_abc123xyz789');
```

You can also set multiple hidden values at once:

```php
<?php

$context->setHidden([
    'api_key'     => 'sk_live_abc123xyz789',
    'api_secret'  => 'secret_key_here',
    'db_password' => 'database_password',
]);
```

### Getting Hidden Data

Retrieve hidden data using `getHidden()`:

```php
<?php

$apiKey = $context->getHidden('api_key');
// $apiKey = 'sk_live_abc123xyz789'
```

The same methods available for regular data also work with hidden data:

```php
<?php

// Get with default value
$apiKey = $context->getHidden('api_key', 'default_key');

// Get only specific hidden keys
$credentials = $context->getOnlyHidden(['api_key', 'api_secret']);

// Get all hidden data except specific keys
$data = $context->getExceptHidden(['db_password']);

// Get all hidden data
$allHidden = $context->getAllHidden();
```

### Checking Hidden Data

Check if a hidden key exists:

```php
<?php

if ($context->hasHidden('api_key')) {
    // API key is set
}
```

### Removing Hidden Data

Remove hidden data using `removeHidden()`:

```php
<?php

// Remove a single hidden value
$context->removeHidden('api_key');

// Remove multiple hidden values
$context->removeHidden(['api_key', 'api_secret']);
```

### Clearing Hidden Data

To clear all hidden data without affecting regular context data:

```php
<?php

$context->clearHidden();
```

To clear both regular and hidden data:

```php
<?php

$context->clearAll();
```

!!! important "Important"
    Regular data and hidden data are stored separately. A key can exist in both regular and hidden storage with different values. Use `get()` for regular data and `getHidden()` for hidden data.

## Integration with Logging

The Context class integrates seamlessly with CodeIgniter's logging system. When enabled, context data is automatically appended to log messages, providing additional information for debugging and monitoring.

### Enabling Global Context Logging

To enable automatic logging of context data, set the `$logGlobalContext` property to `true` in your **app/Config/Logger.php** file:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Logger extends BaseConfig
{
    // ...

    public bool $logGlobalContext = true;

    // ...
}
```

When enabled, all context data (excluding hidden data) will be automatically appended to your log messages as JSON:

```php
<?php

$context = service('context');
$context->set('user_id', 123);
$context->set('transaction_id', 'txn_12345');

log_message('error', 'Payment processing failed');
```

This would produce a log entry like:

```text
ERROR - 2026-02-18 --> Payment processing failed {"user_id":123,"transaction_id":"txn_12345"}
```

!!! note "Note"
    Hidden data set with `setHidden()` is **never** included in logs, even when `$logGlobalContext` is enabled. This ensures sensitive information like API keys or tokens remain secure.

## Important Notes

- Context data persists only for the duration of a single request. It is not shared between requests.
- The Context service is shared by default, meaning there is one instance per request.
- Hidden data is never included in logs, regardless of the logging configuration.
- Regular context data and hidden context data are stored separately and can have overlapping keys.
- Context is cleared automatically at the end of each request.
- In testing environments, remember to clear context data between tests using `clearAll()` to ensure test isolation.

## Class Reference

## CodeIgniter\Context

### Context

#### set($key[, $value = null])

- **Parameters**  
    - **$key** `array|string` The key or an array of key-value pairs
    - **$value** `mixed` The value to store (ignored if `$key` is an array)

- **Returns**: Context instance for method chaining

- **Return type**: `Context`

Sets one or more key-value pairs in the context.

#### setHidden($key[, $value = null])

- **Parameters**  
    - **$key** `array|string` The key or an array of key-value pairs
    - **$value** `mixed` The value to store (ignored if `$key` is an array)

- **Returns**: Context instance for method chaining

- **Return type**: `Context`

Sets one or more key-value pairs in the hidden context.

#### get($key[, $default = null])

- **Parameters**  
    - **$key** `string` The key to retrieve
    - **$default** `mixed` Default value if key doesn't exist

- **Returns**: The value or default

- **Return type**: `mixed`

Gets a value from the context.

#### getHidden($key[, $default = null])

- **Parameters**  
    - **$key** `string` The key to retrieve
    - **$default** `mixed` Default value if key doesn't exist

- **Returns**: The value or default

- **Return type**: `mixed`

Gets a value from the hidden context.

#### getOnly($keys)

- **Parameters**  
    - **$keys** `array|string` Key or array of keys to retrieve

- **Returns**: Array of key-value pairs

- **Return type**: `array`

Gets only the specified keys from the context.

#### getOnlyHidden($keys)

- **Parameters**  
    - **$keys** `array|string` Key or array of keys to retrieve

- **Returns**: Array of key-value pairs

- **Return type**: `array`

Gets only the specified keys from the hidden context.

#### getExcept($keys)

- **Parameters**  
    - **$keys** `array|string` Key or array of keys to exclude

- **Returns**: Array of key-value pairs

- **Return type**: `array`

Gets all context data except the specified keys.

#### getExceptHidden($keys)

- **Parameters**  
    - **$keys** `array|string` Key or array of keys to exclude

- **Returns**: Array of key-value pairs

- **Return type**: `array`

Gets all hidden context data except the specified keys.

#### getAll()

- **Returns**: All context data
- **Return type**: `array`

Gets all data from the context.

#### getAllHidden()

- **Returns**: All hidden context data
- **Return type**: `array`

Gets all data from the hidden context.

#### has($key)

- **Parameters**  
    - **$key** `string` The key to check

- **Returns**: True if key exists, false otherwise

- **Return type**: `bool`

Checks if a key exists in the context.

#### hasHidden($key)

- **Parameters**  
    - **$key** `string` The key to check

- **Returns**: True if key exists, false otherwise

- **Return type**: `bool`

Checks if a key exists in the hidden context.

#### missing($key)

- **Parameters**  
    - **$key** `string` The key to check

- **Returns**: True if key doesn't exist, false otherwise

- **Return type**: `bool`

Checks if a key doesn't exist in the context. Opposite of `has()`.

#### missingHidden($key)

- **Parameters**  
    - **$key** `string` The key to check

- **Returns**: True if key doesn't exist, false otherwise

- **Return type**: `bool`

Checks if a key doesn't exist in the hidden context. Opposite of `hasHidden()`.

#### remove($key)

- **Parameters**  
    - **$key** `array|string` The key or array of keys to remove

- **Returns**: Context instance for method chaining

- **Return type**: `Context`

Removes one or more keys from the context.

#### removeHidden($key)

- **Parameters**  
    - **$key** `array|string` The key or array of keys to remove

- **Returns**: Context instance for method chaining

- **Return type**: `Context`

Removes one or more keys from the hidden context.

#### clear()

- **Returns**: Context instance for method chaining
- **Return type**: `Context`

Clears all data from the context (does not affect hidden data).

#### clearHidden()

- **Returns**: Context instance for method chaining
- **Return type**: `Context`

Clears all data from the hidden context (does not affect regular data).

#### clearAll()

- **Returns**: Context instance for method chaining
- **Return type**: `Context`

Clears all data from both the context and hidden context.
