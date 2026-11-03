# Upgrading from 4.2.10 to 4.2.11

Please refer to the upgrade instructions corresponding to your installation method.

- [Composer Installation App Starter Upgrading](installing_composer.md#upgrading)
- [Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading](installing_composer.md#upgrading_1)
- [Manual Installation Upgrading](installing_manual.md#upgrading)

## Breaking Changes

### Config\App::$proxyIPs

The config value format has been changed. Now you must set your proxy IP address and the HTTP header name for the client IP address pair as an array:

```
public $proxyIPs = [
        '10.0.1.200'     => 'X-Forwarded-For',
        '192.168.5.0/24' => 'X-Forwarded-For',
];
```

`ConfigException` will be thrown for a config value in the old format.

### Session Handler Key Changes

The key of the session data record for [DatabaseHandler Driver](../libraries/sessions.md#databasehandler-driver), [MemcachedHandler Driver](../libraries/sessions.md#memcachedhandler-driver) and [RedisHandler Driver](../libraries/sessions.md#redishandler-driver) has changed. Therefore, any existing session data will be invalidated after the upgrade if you are using these session handlers.

- When using `DatabaseHandler`, the `id` column value in the session table now contains the session cookie name (`Config\App::$sessionCookieName`).
- When using `MemcachedHandler` or `RedisHandler`, the key value contains the session cookie name (`Config\App::$sessionCookieName`).

There is a maximum length for the `id` column and Memcached key (250 bytes). If the following values exceed those maximum lengths, the session will not work properly.

- the session cookie name, delimiter, and session id (32 characters by default) when using `DatabaseHandler`
- the prefix (`ci_session`), session cookie name, delimiters, and session id when using `MemcachedHandler`

## Project Files

Version 4.2.11 did not alter any executable code in project files.

### All Changes

This is a list of all files in the **project space** that received changes; many will be simple comments or formatting that have no effect on the runtime:

- **app/Config/App.php**
- **app/Config/Autoload.php**
- **app/Config/Logger.php**
- **app/Config/Toolbar.php**
- **app/Views/welcome_message.php**
- **composer.json**
- **phpunit.xml.dist**
