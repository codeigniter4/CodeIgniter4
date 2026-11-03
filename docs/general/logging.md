# Logging Information

## Log Levels

You can log information to the local log files by using the `log_message()` function. You must supply the "level" of the error in the first parameter, indicating what type of message it is (debug, error, etc.). The second parameter is the message itself:

```php
<?php

if ($some_var === '') {
    log_message('error', 'Some variable did not contain a value.');
}
```

There are eight different log levels, matching the [RFC 5424](https://tools.ietf.org/html/rfc5424) levels, and they are as follows:

| Level | Description |
|----|----|
| debug | Detailed debug information. |
| info | Interesting events in your application, like a user logging in, logging SQL queries, etc. |
| notice | Normal but significant events in your application. |
| warning | Exceptional occurrences that are not errors, like the use of deprecated APIs, poor use of an API, or other undesirable things that are not necessarily wrong. |
| error | Runtime errors that do not require immediate action but should typically be logged and monitored. |
| critical | Critical conditions, like an application component not available, or an unexpected exception. |
| alert | Action must be taken immediately, like when an entire website is down, the database unavailable, etc. |
| emergency | The system is unusable. |

The logging system does not provide ways to alert sysadmins or webmasters about these events. It solely logs the information. For many of the more critical event levels, the logging is done automatically by the [Error Handler](errors.md).

## Configuration

You can modify which levels are actually logged, as well as assign different Loggers to handle different levels, within the **app/Config/Logger.php** configuration file.

The `threshold` value of the config file determines which levels are logged across your application. If any levels are requested to be logged by the application, but the threshold doesn't allow them to log currently, they will be ignored. The simplest method to use is to set this value to the minimum level that you want to have logged. For example, if you want to log warning messages, and not information messages, you would set the threshold to `5`. Any log requests with a level of 5 or less (which includes runtime errors, system errors, etc.) would be logged, and info, notices, and debug would be ignored:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Logger extends BaseConfig
{
    public $threshold = 5;

    // ...
}
```

A complete list of levels and their corresponding threshold value is in the configuration file for your reference.

You can pick and choose the specific levels that you would like logged by assigning an array of log level numbers to the threshold value:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Logger extends BaseConfig
{
    // Log only debug and info type messages
    public $threshold = [5, 8];

    // ...
}
```

### Using Multiple Log Handlers

The logging system can support multiple logging methods running at the same time. Each handler can be set to handle specific levels and ignore the rest. Currently, three handlers come with a default install:

- **File Handler** is the default handler and will create a single file for every day locally. This is the recommended method of logging.
- **ChromeLogger Handler**: If you have the [ChromeLogger extension](https://craig.is/writing/chrome-logger) installed in the Chrome web browser, you can use this handler to display the log information in Chrome's console window.
- **Errorlog Handler**: This handler will take advantage of PHP's native `error_log()` function and write the logs there. Currently, only the `0` and `4` message types of `error_log()` are supported.

The handlers are configured in the main configuration file, in the `$handlers` property, which is simply an array of handlers and their configuration. Each handler is specified with the key being the fully namespaced class name. The value will be an array of varying properties, specific to each handler. Each handler's section will have one property in common: `handles`, which is an array of log level *names* that the handler will log information for.

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Logger extends BaseConfig
{
    public $handlers = [
        // File Handler
        'CodeIgniter\Log\Handlers\FileHandler' => [
            'handles' => ['critical', 'alert', 'emergency', 'debug', 'error', 'info', 'notice', 'warning'],
        ],
    ];

    // ...
}
```

### Stopping the Handler Chain on Failure

!!! success "Available from version 4.8.0"

Handlers are executed in the order they are defined in the `$handlers` property. When a handler's `handle()` method returns `false`, the handlers defined after it are not executed.

By default, the **File Handler** and the **Errorlog Handler** return `false` when they fail to write the log, for example when the log directory is not writable. This means that a failing handler prevents the handlers after it from logging the message.

If you want the remaining handlers to run even when a handler fails to write the log, set its `stopChainOnFailure` option to `false`:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Logger extends BaseConfig
{
    public $handlers = [
        // File Handler
        'CodeIgniter\Log\Handlers\FileHandler' => [
            'handles'            => ['critical', 'alert', 'emergency', 'debug', 'error', 'info', 'notice', 'warning'],
            'stopChainOnFailure' => false,
        ],
        // Errorlog Handler
        'CodeIgniter\Log\Handlers\ErrorlogHandler' => [
            'handles' => ['critical', 'alert', 'emergency', 'debug', 'error', 'info', 'notice', 'warning'],
        ],
    ];

    // ...
}
```

In the example above, the **Errorlog Handler** still logs the message when the **File Handler** cannot write to the log file.

!!! note "Note"
    This option only affects failed writes in the **File Handler** and the **Errorlog Handler**. A custom handler can still return `false` from `handle()` to stop the remaining handlers.

## Modifying the Message with Context

You will often want to modify the details of your message based on the context of the event being logged. You might need to log a user ID, an IP address, the current POST variables, etc. You can do this by using placeholders in your message. Each placeholder must be wrapped in curly braces. In the third parameter, you must provide an array of placeholder names (without the braces) and their values. These will be inserted into the message string:

```php
<?php

// Generates a message like: User 123 logged into the system from 127.0.0.1
$info = [
    'id'         => $user->id,
    'ip_address' => $this->request->getIPAddress(),
];

log_message('info', 'User {id} logged into the system from {ip_address}', $info);
```

If you want to log an Exception or an Error, you can use the key 'exception', with the value being the Exception or Error itself. A string will be generated from that object containing the error message, the file name and line number. You must still provide the exception placeholder in the message:

```php
<?php

try {
    // Something throws error here
} catch (\Exception $e) {
    log_message('error', '[ERROR] {exception}', ['exception' => $e]);
}
```

Several core placeholders exist that will be automatically expanded for you based on the current page request:

| Placeholder    | Inserted value                                 |
|----------------|------------------------------------------------|
| {post_vars}    | $_POST variables                              |
| {get_vars}     | $_GET variables                               |
| {session_vars} | $_SESSION variables                           |
| {env}          | Current environment name, i.e., development    |
| {file}         | The name of the file calling the logger        |
| {line}         | The line in {file} where the logger was called |
| {env:foo}      | The value of 'foo' in $_ENV                   |

### Global Context Logging

!!! success "Available from version 4.8.0"

You can automatically append context data to all log messages by enabling the `$logGlobalContext` property in **app/Config/Logger.php**:

```php
<?php

$context = service('context');
$context->set('user_id', 123);
$context->set('transaction_id', 'txn_12345');

log_message('error', 'Payment processing failed');
```

When enabled, all regular context data (set via the [Context class](context.md#context)) is automatically appended to every log message as a JSON string:

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
    Hidden data set with `setHidden()` is **never** included in log output, even when `$logGlobalContext` is enabled. This protects sensitive information such as API keys and tokens from appearing in log files.

See [Context](context.md#context) for full documentation on storing and managing context data.

### Per-Call Context Logging

!!! success "Available from version 4.8.0"

By default, context values passed to `log_message()` are only used for placeholder interpolation and are not stored anywhere. You can enable structured context logging by setting `$logContext = true` in **app/Config/Logger.php**:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Logger extends BaseConfig
{
    // Enable passing non-interpolated context keys to handlers as structured data.
    public bool $logContext = true;

    // Include the stack trace when a Throwable is found in the context.
    public bool $logContextTrace = true;

    // Also pass context keys that were used as {placeholder} in the message.
    public bool $logContextUsedKeys = false;
}
```

When enabled, any context key that is **not** referenced as a `{placeholder}` in the message is passed to handlers as structured data. Keys that were interpolated into the message are stripped by default (since their values are already present in the message text), but you can keep them by setting `$logContextUsedKeys = true`.

```php
<?php

// With $logContext = true in Config\Logger.
// 'user_id' is not used as a placeholder, so it is passed to handlers as structured data.
log_message('error', 'Payment failed for order {order_id}', [
    'order_id' => 'ord_999', // interpolated into the message, stripped from context by default
    'user_id'  => 42,        // not in message, kept and passed to handlers
]);

// Handlers receive context: ['user_id' => 42]
```

**Throwable normalization**

Per PSR-3, a `Throwable` instance must be passed under the `exception` key to be handled specially. When found there, it is automatically normalized into a meaningful array instead of being serialized as an empty object:

```php
<?php

// With $logContext = true in Config\Logger.
// The exception is not used as a placeholder, so it is normalized and passed to handlers.
try {
    // Something throws an error here
} catch (\Exception $e) {
    log_message('error', 'Payment processing failed', ['exception' => $e]);
}

// Handlers receive context:
// [
//     'exception' => [
//         'class'   => 'RuntimeException',
//         'message' => 'Something went wrong',
//         'code'    => 0,
//         'file'    => 'app/Controllers/Payment.php',
//         'line'    => 42,
//     ]
// ]
```

The normalized array contains `class`, `message`, `code`, `file`, and `line`. To also include the full stack trace, set `$logContextTrace = true`.

!!! note "Note"
    `$logContext` and `$logGlobalContext` are independent. You can enable either or both. When both are enabled, per-call context and global context are merged before being passed to handlers.

## Using Third-Party Loggers

You can use any other logger that you might like as long as it implements `Psr\Log\LoggerInterface` and is [PSR-3](https://www.php-fig.org/psr/psr-3/) compatible. This means that you can easily drop in any PSR-3 compatible logger, or create your own.

You must ensure that the third-party logger can be found by the system, either by adding it to the **app/Config/Autoload.php** configuration file, or through another autoloader, like Composer. Next, you should modify **app/Config/Services.php** to point the `logger` alias to your new class name.

Now, any call that is done through the `log_message()` function will use your library instead.
