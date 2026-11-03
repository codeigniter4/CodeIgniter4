# Connecting to your Database

## Connecting to a Database

### Connecting to the Default Group

You can connect to your database by adding this line of code in any function where it is needed, or in your class constructor to make the database available globally in that class.

```php
$db = \Config\Database::connect();
```

If the above function does **not** contain any information in the first parameter, it will connect to the default group specified in your database config file. For most people, this is the preferred method of use.

A convenience method exists that is purely a wrapper around the above line and is provided for your convenience:

```php
$db = db_connect();
```

### Available Parameters

**\Config\Database::connect($group = null, bool $getShared = true): BaseConnection**

1.  `$group`: The database group name, a string that must match the config class' property name. Default value is `Config\Database::$defaultGroup`.
2.  `$getShared`: true/false (boolean). Whether to return the shared connection (see Connecting to Multiple Databases below).

### Connecting to Specific Group

The first parameter of this function can **optionally** be used to specify a particular database group from your config file.

To choose a specific group from your config file, you can do this:

```php
$db = \Config\Database::connect('group_name');
```

Here, `group_name` is the name of the connection group from your config file.

### Multiple Connections to Same Database

By default, the `connect()` method will return the same instance of the database connection every time. If you need to have a separate connection to the same database, send `false` as the second parameter:

```php
$db = \Config\Database::connect('group_name', false);
```

## Connecting to Multiple Databases

If you need to connect to more than one database simultaneously, you can do so as follows:

```php
$db1 = \Config\Database::connect('group_one');
$db2 = \Config\Database::connect('group_two');
```

Note: Change the words `group_one` and `group_two` to the specific group names you are connecting to.

!!! note "Note"
    You don't need to create separate database configurations if you only need to use a different database on the same connection. You can switch to a different database when you need to, like this: `$db->setDatabase($database2_name);`

## Connecting with Custom Settings

You can pass in an array of database settings instead of a group name to get a connection that uses your custom settings. The array passed in must be in the same format as the groups defined in the configuration file:

```php
$custom = [
    'DSN'      => '',
    'hostname' => 'localhost',
    'username' => '',
    'password' => '',
    'database' => '',
    'DBDriver' => 'MySQLi',
    'DBPrefix' => '',
    'pConnect' => false,
    'DBDebug'  => true,
    'charset'  => 'utf8mb4',
    'DBCollat' => 'utf8mb4_general_ci',
    'swapPre'  => '',
    'encrypt'  => false,
    'compress' => false,
    'strictOn' => false,
    'failover' => [],
    'port'     => 3306,
];
$db = \Config\Database::connect($custom);
```

## Reconnecting / Keeping the Connection Alive

If the database server's idle timeout is exceeded while you're doing some heavy PHP lifting (processing an image, for instance), you should consider calling the `reconnect()` method before sending further queries, which can gracefully keep the connection alive or re-establish it.

The `reconnect()` method pings the server to check if the connection is still alive. If the connection has been lost, it will close and re-establish the connection. An open transaction ends with the old connection. `reconnect()` clears the transaction depth and marks the transaction failed before it opens the new connection, so `transComplete()` does not report success. On OCI8, commit mode returns to autocommit.

The transaction-state reset described above only occurs when `ping()` returns false. On PostgreSQL, `ping()` uses `pg_ping()`, which can re-establish a broken connection and return true. In that case, `reconnect()` does not clear the transaction state.

The lost transaction is not resumed on the new connection. Subsequent writes may be committed automatically and cannot be rolled back by calling `transComplete()`. If the operation must remain atomic, stop executing its remaining queries. Handle the failure at the application level that owns the entire transaction, including any nested transaction groups, before deciding whether to retry the entire operation.

```php
$db->reconnect();
```

## Manually Closing the Connection

While CodeIgniter intelligently takes care of closing your database connections, you can explicitly close the connection.

```php
$db->close();
```
