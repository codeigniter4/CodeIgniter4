# Upgrading from 4.7.6 to 4.7.7

Please refer to the upgrade instructions corresponding to your installation method.

- [Composer Installation App Starter Upgrading](installing_composer.md#upgrading)
- [Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading](installing_composer.md#upgrading_1)
- [Manual Installation Upgrading](installing_manual.md#upgrading)

## Mandatory File Changes

## Breaking Changes

### File Cache Rejects Directory Separators

For security reasons, `FileHandler::validateKey()` now rejects `/`, `\` and null bytes in the cache item file name, independently of the `Config\Cache::$reservedCharacters` setting. A cache key can no longer point outside the cache directory when that setting is relaxed.

With the default configuration most applications are unaffected, because `/`, `\` and `:` are already reserved characters. Applications that emptied `$reservedCharacters` to share one key namespace between handlers and used directory separators in File cache keys now get an `InvalidArgumentException`. Build keys from identifiers with a controlled format, or hash the input, instead of relaxing `$reservedCharacters`. See [Caching](../libraries/caching.md).

## Breaking Enhancements

## Behavior Changes

### Worker Mode Routes Are Reloaded Per Request

`Config\WorkerMode::$persistentServices` no longer contains `routes`.

`RouteCollection` reads the request host while it loads the route definitions, and drops the routes limited with the `hostname` or `subdomain` options that do not match the current request. While the routes service was persistent, those decisions were made once, from the first request the worker served, and were kept for the lifetime of the process. A route limited to a hostname then became reachable from any `Host` after that first request, and a route that lost the first decision stayed unreachable until the worker restarted. The routes service is not persistent anymore, so the route definitions are loaded for each request and the host is evaluated on every request, as in traditional PHP.

A new installation gets the updated default. Existing installations have their own copy of **app/Config/WorkerMode.php** in the project space, so it is not changed on upgrade. If your application uses the `hostname` or `subdomain` route options in Worker Mode, remove `routes` from `$persistentServices`:

```php
<?php

namespace Config;

class WorkerMode
{
    // ...
    /**
     * @var list<string>
     */
    public array $persistentServices = [
        'autoloader',
        'locator',
        'exceptions',
        'commands',
        'codeigniter',
        'superglobals',
        'cache',
    ];
}
```

See [Persistent Services](worker_mode.md#persistent-services) for details.

## Project Files

Some files in the **project space** (root, app, public, writable) received updates. Due to these files being outside of the **system** scope they will not be changed without your intervention.

!!! note "Note"
    There are some third-party CodeIgniter modules available to assist with merging changes to the project space: [Explore on Packagist](https://packagist.org/explore/?query=codeigniter4%20updates).

### Content Changes

The following files received significant changes (including deprecations or visual adjustments) and it is recommended that you merge the updated versions with your application:

#### Config

- **app/Config/WorkerMode.php**
    - `Config\WorkerMode::$persistentServices` no longer contains `routes`. See [Worker Mode Routes Are Reloaded Per Request](#worker-mode-routes-are-reloaded-per-request).

### All Changes

This is a list of all files in the **project space** that received changes; many will be simple comments or formatting that have no effect on the runtime:

- @TODO
