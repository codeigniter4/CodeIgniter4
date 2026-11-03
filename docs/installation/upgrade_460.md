# Upgrading from 4.5.8 to 4.6.0

Please refer to the upgrade instructions corresponding to your installation method.

- [Composer Installation App Starter Upgrading](installing_composer.md#upgrading)
- [Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading](installing_composer.md#upgrading_1)
- [Manual Installation Upgrading](installing_manual.md#upgrading)

## Breaking Changes

### Exception Changes

Some classes have changed the exception classes that are thrown. Some exception classes have changed parent classes. See [ChangeLog](../changelogs/v4.6.0.md#exceptions) for details.

If you have code that catches these exceptions, change the exception classes.

### Time::createFromTimestamp() Timezone Change

When you do not explicitly pass a timezone, now [Time::createFromTimestamp()](../libraries/time.md#createfromtimestamp) returns a Time instance with **UTC**. From v4.4.6 to before v4.6.0, a Time instance with the currently set default timezone was returned.

This behavior change normalizes behavior with changes in PHP 8.4, which adds a new `DateTimeInterface::createFromTimestamp()` method.

If you want to keep the default timezone, you need to pass the timezone as the second parameter:

```
use CodeIgniter\I18n\Time;

$time = Time::createFromTimestamp(1501821586, date_default_timezone_get());
```

### Time keeps Microseconds

In previous versions, [Time](../libraries/time.md) lost microseconds in some cases. But the bugs have been fixed.

The results of the `Time` comparison may differ due to these fixes:

```php
use CodeIgniter\I18n\Time;

$time1 = new Time('2024-01-01 12:00:00.654321');
$time2 = new Time('2024-01-01 12:00:00');

$time1->equals($time2);
// Before: true
//  After: false
```

In such a case, you need to remove the microseconds:

```php
use CodeIgniter\I18n\Time;

$time1 = new Time('2024-01-01 12:00:00.654321');
$time2 = new Time('2024-01-01 12:00:00');

// Removes the microseconds.
$time1 = Time::createFromFormat(
    'Y-m-d H:i:s',
    $time1->format('Y-m-d H:i:s'),
    $time1->getTimezone(),
);

$time1->equals($time2);
// Before: true
//  After: true
```

The following cases now keep microseconds:

```php
use CodeIgniter\I18n\Time;

$time = Time::createFromFormat('Y-m-d H:i:s.u', '2024-07-09 09:13:34.654321');
echo $time->format('Y-m-d H:i:s.u');
// Before: 2024-07-09 09:13:34.000000
//  After: 2024-07-09 09:13:34.654321
```

```php
use CodeIgniter\I18n\Time;

$time = new Time('1 hour ago');
echo $time->format('Y-m-d H:i:s.u');
// Before: 2024-07-26 21:05:57.000000
//  After: 2024-07-26 21:05:57.857235
```

Note that `Time` with the current time already held microseconds before this change.

```php
use CodeIgniter\I18n\Time;

$time = Time::now();
echo $time->format('Y-m-d H:i:s.u');
// Before: 2024-07-26 21:39:32.249072
//  After: 2024-07-26 21:39:32.249072
```

Also, methods that return an `int` still lose the microseconds.

```php
use CodeIgniter\I18n\Time;

$time1 = new Time('2024-01-01 12:00:00');
echo $time1->getTimestamp(); // 1704110400

$time2 = new Time('2024-01-01 12:00:00.654321');
echo $time2->getTimestamp(); // 1704110400
```

### Time::setTimestamp() Behavior Fix

In previous versions, calling `Time::setTimestamp()` on a Time instance with a timezone other than the default timezone might return a Time instance with the wrong date/time.

This bug has been fixed, and it now behaves in the same way as `DateTimeImmutable`:

```php
use CodeIgniter\I18n\Time;

// The Application Timezone is "UTC".

// Set $time1 timezone to "America/Chicago".
$time1 = Time::parse('2024-08-20', 'America/Chicago');

// The timestamp is "2024-08-20 00:00" in "UTC".
$stamp = strtotime('2024-08-20'); // 1724112000

// But $time2 timezone is "America/Chicago".
$time2 = $time1->setTimestamp($stamp);

echo $time2->format('Y-m-d H:i:s P');
// Before: 2024-08-20 00:00:00 -05:00
//  After: 2024-08-19 19:00:00 -05:00
```

Note that if you use the default timezone, the behavior is not changed:

```php
use CodeIgniter\I18n\Time;

// The Application Timezone is "America/Chicago".

// $time1 timezone is "America/Chicago".
$time1 = Time::parse('2024-08-20');

// The timestamp is "2024-08-20 00:00" in "America/Chicago".
$stamp = strtotime('2024-08-20'); // 1724130000

// $time2 timezone is also "America/Chicago".
$time2 = $time1->setTimestamp($stamp);

echo $time2->format('Y-m-d H:i:s P');
// Before: 2024-08-20 00:00:00 -05:00
//  After: 2024-08-20 00:00:00 -05:00
```

### Registrars with Dirty Hack

To prevent Auto-Discovery of [Registrars](../general/configuration.md#registrars) from running twice, when a registrar class is loaded or instantiated, if it instantiates a Config class (which extends `CodeIgniter\Config\BaseConfig`), `ConfigException` will be raised.

This is because if Auto-Discovery of Registrars is performed twice, duplicate values may be added to properties of Config classes.

All registrar classes (**Config/Registrar.php** in all namespaces) must be modified so that they do not instantiate any Config class when loaded or instantiated.

If the packages/modules you are using provide such registrar classes, the registrar classes in the packages/modules need to be fixed.

The following is an example of code that will no longer work:

```php
<?php

namespace CodeIgniter\Shield\Config;

use Config\App;

class Registrar
{
    public function __construct()
    {
        $config = new App(); // Bad. When this class is instantiated, Config\App will be instantiated.

        // Does something.
    }

    public static function Pager(): array
    {
        return [
            'templates' => [
                'module_pager' => 'MyModule\Views\Pager',
            ],
        ];
    }

    public static function hack(): void
    {
        $config = config('Cache');

        // Does something.
    }
}

Registrar::hack(); // Bad. When this class is loaded, Config\Cache will be instantiated.
```

### Session ID (SID) Change

Now [Session Library](../libraries/sessions.md) forces the use of the PHP default 32-character SIDs, with 4 bits of entropy per character. This change is to match the behavior of PHP 9.

In other words, the following settings are always used:

```ini
session.sid_bits_per_character = 4
session.sid_length = 32
```

In previous versions, the PHP ini settings were respected. So this change may change your SID length.

If you cannot accept this change, customize the Session library.

### Interface Changes

Some interface changes have been made. Classes that implement them should update their APIs to reflect the changes. See [ChangeLog](../changelogs/v4.6.0.md#headers) for details.

### Method Signature Changes

Some method signature changes have been made. Classes that extend them should update their APIs to reflect the changes. See [ChangeLog](../changelogs/v4.6.0.md#method-signature-changes) for details.

### Removed Deprecated Items

Some deprecated items have been removed. If you are still using these items, or extending these classes, upgrade your code. See [ChangeLog](../changelogs/v4.6.0.md#removed-deprecated-items) for details.

## Breaking Enhancements

### Filters Changes

The `Filters` class has been changed to allow multiple runs of the same filter with different arguments in before or after.

If you are extending `Filters`, you will need to modify it to conform to the following changes:

- The structure of the array properties `$filters` and `$filtersClasses` has been changed.
- The properties `$arguments` and `$argumentsClass` are no longer used.
- `Filters` has been changed so that the same filter class is not instantiated multiple times. If a filter class is used both before and after, the same instance is used.

## Project Files

Some files in the **project space** (root, app, public, writable) received updates. Due to these files being outside of the **system** scope, they will not be changed without your intervention.

There are some third-party CodeIgniter modules available to assist with merging changes to the project space: [Explore on Packagist](https://packagist.org/explore/?query=codeigniter4%20updates).

### Content Changes

The following files received significant changes (including deprecations or visual adjustments) and it is recommended that you merge the updated versions with your application:

#### Config

- **app/Config/Feature.php**
    - `Config\Feature::$autoRoutesImproved` has been changed to `true`.
    - `Config\Feature::$strictLocaleNegotiation` has been added.
- **app/Config/Routing.php**
    - `Config\Routing::$translateUriToCamelCase` has been changed to `true`.
- **app/Config/Kint.php**
    - `Config\Kint::$richSort` has been removed. Kint in v6 no longer uses `AbstractRenderer::SORT_FULL`. Leaving this property in your code will cause a runtime error due to the undefined constant.

### All Changes

This is a list of all files in the **project space** that received changes; many will be simple comments or formatting that have no effect on the runtime:

- **app/Config/Cache.php**
- **app/Config/Constants.php**
- **app/Config/Database.php**
- **app/Config/Feature.php**
- **app/Config/Format.php**
- **app/Config/Kint.php**
- **app/Config/Routing.php**
- **app/Config/Security.php**
- **app/Views/errors/html/debug.css**
- **app/Views/errors/html/error_400.php**
- **preload.php**
- **public/index.php**
- spark
