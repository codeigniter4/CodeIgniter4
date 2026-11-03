# Queries

## Query Basics

!!! note "Note"
    CodeIgniter doesn't support dots (`.`) in the table and column names. Since v4.5.0, database names with dots are supported.

### Regular Queries

#### $db->query()

To submit a query, use the `query()` method:

```php
<?php

$db = db_connect();
$db->query('YOUR QUERY HERE');
```

The `query()` method returns a database result **object** when "read" type queries are run, and you can use that object to [show your results](results.md). When "write" type queries are run, it simply returns true or false depending on success or failure. When retrieving data, you will typically assign the query to your own variable, like this:

```php
<?php

$query = $db->query('YOUR QUERY HERE');
```

!!! note "Note"
    If you are using the OCI8 driver, SQL statements should not end with a semi-colon (`;`). PL/SQL statements should end with a semi-colon (`;`).

### Simplified Queries

#### $db->simpleQuery()

The `simpleQuery()` method is a simplified version of the `$db->query()` method. It DOES NOT return a database result set, nor does it set the query timer, or compile bind data, or store your query for debugging. It simply lets you submit a query. Most users will rarely use this function.

It returns whatever the database driver's "execute" function returns. That typically is true/false on success or failure for write type queries such as INSERT, DELETE or UPDATE statements (which is what it really should be used for) and a resource/object on success for queries with fetchable results.

```php
<?php

if ($db->simpleQuery('YOUR QUERY')) {
    echo 'Success!';
} else {
    echo 'Query failed!';
}
```

!!! note "Note"
    PostgreSQL's `pg_exec()` function (for example) always returns a resource on success even for write type queries. So keep that in mind if you're looking for a boolean value.

## Working with Database Prefixes Manually

### $db->prefixTable()

If you have configured a database prefix and would like to prepend it to a table name for use in a native SQL query, for example, then you can use the following:

```php
<?php

$db->prefixTable('tablename'); // outputs prefix_tablename
```

### $db->setPrefix()

If for any reason you would like to change the prefix programmatically without needing to create a new connection, you can use this method:

```php
<?php

$db->setPrefix('newprefix_');
$db->prefixTable('tablename'); // outputs newprefix_tablename
```

### $db->getPrefix()

You can get the current prefix any time with this method:

```php
<?php

$DBPrefix = $db->getPrefix();
```

## Protecting Identifiers

### $db->protectIdentifiers()

In many databases, it is advisable to protect table and field names - for example with backticks in MySQL. **Query Builder queries are automatically protected**, but if you need to manually protect an identifier you can use:

```php
<?php

$db->protectIdentifiers('table_name');
```

!!! important "Important"
    Although the Query Builder will try its best to properly quote any field and table names that you feed it, note that it is NOT designed to work with arbitrary user input. DO NOT feed it with unsanitized user data.

This function will also add a **table prefix** to your table, assuming you have a prefix specified in your database config file. To enable prefixing, set `true` (boolean) via the second parameter:

```php
<?php

$db->protectIdentifiers('table_name', true);
```

## Escaping Values

It's a very good security practice to escape your data before submitting it into your database. CodeIgniter has three methods that help you do this:

### 1. $db->escape()

This function determines the data type so that it can escape only string data. It also automatically adds single quotes around the data so you don't have to:

```php
<?php

$sql = 'INSERT INTO table (title) VALUES(' . $db->escape($title) . ')';
```

### 2. $db->escapeString()

This function escapes the data passed to it, regardless of type. Most of the time you'll use the above function rather than this one. Use the function like this:

```php
<?php

$sql = "INSERT INTO table (title) VALUES('" . $db->escapeString($title) . "')";
```

### 3. $db->escapeLikeString()

This method should be used when strings are to be used in LIKE conditions so that LIKE wildcards (`%`, `_`) in the string are also properly escaped.

```php
<?php

$search = '20% raise';
$sql    = "SELECT id FROM table WHERE column LIKE '%" . $db->escapeLikeString($search) . "%' ESCAPE '!'";
```

!!! important "Important"
    The `escapeLikeString()` method uses `'!'` (exclamation mark) to escape special characters for `LIKE` conditions. Because this method escapes partial strings that you would wrap in quotes yourself, it cannot automatically add the `ESCAPE '!'` condition for you, and so you'll have to manually do that.

## Query Bindings

Bindings enable you to simplify your query syntax by letting the system put the queries together for you. Consider the following example:

```php
<?php

$sql = 'SELECT * FROM some_table WHERE id = ? AND status = ? AND author = ?';
$db->query($sql, [3, 'live', 'Rick']);
```

The question marks in the query are automatically replaced with the values in the array in the second parameter of the query function.

Bindings also work with arrays, which will be transformed to IN sets:

```php
<?php

$sql = 'SELECT * FROM some_table WHERE id IN ? AND status = ? AND author = ?';
$db->query($sql, [[3, 6], 'live', 'Rick']);
```

The resulting query will be:

```
SELECT * FROM some_table WHERE id IN (3,6) AND status = 'live' AND author = 'Rick'
```

The secondary benefit of using binds is that the values are automatically escaped, producing safer queries. You don't have to remember to manually escape data - the engine does it automatically for you.

### Named Bindings

Instead of using the question mark to mark the location of the bound values, you can name the bindings, allowing the keys of the values passed in to match placeholders in the query:

```php
<?php

$sql = 'SELECT * FROM some_table WHERE id = :id: AND status = :status: AND author = :name:';
$db->query($sql, [
    'id'     => 3,
    'status' => 'live',
    'name'   => 'Rick',
]);
```

!!! note "Note"
    Each name in the query MUST be surrounded by colons.

!!! note "Note"
    Placeholders are only recognized where the SQL parser would see them as part of the query. A placeholder inside a quoted string, a quoted identifier, or a SQL comment is left untouched. If you need a value inside a quoted string, for example in a `LIKE` pattern, put the whole string into the bound value (`'name' => '%Rick%'`) and use the placeholder without quotes.

## Handling Errors

### $db->error()

If you need to get the last error that has occurred, the `error()` method will return an array containing its code and message. Here's a quick example:

```php
<?php

if (! $db->simpleQuery('SELECT `example_field` FROM `example_table`')) {
    $error = $db->error(); // Has keys 'code' and 'message'
}
```

## Prepared Queries

Most database engines support some form of prepared statements that allow you to prepare a query once, and then run that query multiple times with new sets of data. This eliminates the possibility of SQL injection since the data is passed to the database in a different format than the query itself. When you need to run the same query multiple times, it can be quite a bit faster, too. However, to use it for every query can have major performance hits, since you're calling out to the database twice as often. Since the Query Builder and Database connections already handle escaping the data for you, the safety aspect is already taken care of for you. There will be times, though, when you need the ability to optimize the query by running a prepared statement, or prepared query.

### Preparing the Query

This can be easily done with the `prepare()` method. This takes a single parameter, which is a Closure that returns a query object. Query objects are automatically generated by any of the "final" type queries, including **insert**, **update**, **delete**, **replace**, and **get**. The easiest way to handle this is to use the Query Builder to run a query. The query is not actually run, and the values don't matter since they're never applied, acting instead as placeholders. This returns a PreparedQuery object:

```php
<?php

$pQuery = $db->prepare(static fn ($db) => $db->table('user')->insert([
    'name'    => 'x',
    'email'   => 'y',
    'country' => 'US',
]));
```

If you don't want to use the Query Builder, you can create the Query object manually using question marks for value placeholders:

```php
<?php

use CodeIgniter\Database\Query;

$pQuery = $db->prepare(static function ($db) {
    $sql = 'INSERT INTO user (name, email, country) VALUES (?, ?, ?)';

    return (new Query($db))->setQuery($sql);
});
```

If the database requires an array of options passed to it during the prepare statement phase, you can pass that array through in the second parameter:

```php
<?php

use CodeIgniter\Database\Query;

$pQuery = $db->prepare(static function ($db) {
    $sql = 'INSERT INTO user (name, email, country) VALUES (?, ?, ?)';

    return (new Query($db))->setQuery($sql);
}, $options);
```

!!! note "Note"
    Currently, the only database that actually uses the array of options is SQLSRV.

### Executing the Query

Once you have a prepared query, you can use the `execute()` method to actually run the query. You can pass in as many variables as you need in the query parameters. The number of parameters you pass must match the number of placeholders in the query. They must also be passed in the same order as the placeholders appear in the original query:

```php
<?php

// Prepare the Query
$pQuery = $db->prepare(static fn ($db) => $db->table('user')->insert([
    'name'    => 'x',
    'email'   => 'y',
    'country' => 'US',
]));

// Collect the Data
$name    = 'John Doe';
$email   = 'j.doe@example.com';
$country = 'US';

// Run the Query
$results = $pQuery->execute($name, $email, $country);
```

For queries of type "write", it returns true or false, indicating the success or failure of the query. For queries of type "read", it returns a standard [result set](results.md).

### Other Methods

In addition to these two primary methods, the prepared query object also has the following methods:

#### close()

While PHP does a pretty good job of closing all open statements with the database, it's always a good idea to close out the prepared statement when you're done with it:

```php
<?php

if ($pQuery->close()) {
    echo 'Success!';
} else {
    echo 'Deallocation of prepared statements failed!';
}
```

!!! note "Note"
    Since v4.3.0, the `close()` method deallocates the prepared statement in all DBMS. Previously, they were not deallocated in Postgre, SQLSRV and OCI8.

#### getQueryString()

This returns the prepared query as a string.

#### hasError()

Returns boolean true/false if the last `execute()` call created any errors.

#### getErrorCode()

#### getErrorMessage()

If any errors were encountered, these methods can be used to retrieve the error code and string.

## Working with Query Objects

Internally, all queries are processed and stored as instances of `CodeIgniter\Database\Query`. This class is responsible for binding the parameters, otherwise preparing the query, and storing performance data about its query.

### $db->getLastQuery()

When you just need to retrieve the last Query object, use the `getLastQuery()` method:

```php
<?php

$query = $db->getLastQuery();
echo (string) $query;
```

### The Query Class

Each query object stores several pieces of information about the query itself. This is used, in part, by the Timeline feature, but is available for your use as well.

#### getQuery()

Returns the final query after all processing has happened. This is the exact query that was sent to the database:

```php
<?php

$sql = $query->getQuery();
```

This same value can be retrieved by casting the Query object to a string:

```php
<?php

$sql = (string) $query;
```

#### getOriginalQuery()

Returns the raw SQL that was passed into the object. This will not have any binds in it, or prefixes swapped out, etc.:

```php
<?php

$sql = $query->getOriginalQuery();
```

#### hasError()

If an error was encountered during the execution of this query, this method will return true:

```php
<?php

if ($query->hasError()) {
    echo 'Code: ' . $query->getErrorCode();
    echo 'Error: ' . $query->getErrorMessage();
}
```

#### isWriteType()

Returns true if the query was determined to be a write-type query (i.e., INSERT, UPDATE, DELETE, etc.):

```php
<?php

if ($query->isWriteType()) {
    // ... do something
}
```

#### swapPrefix()

Replaces one table prefix with another value in the SQL. The first parameter is the original prefix that you want replaced, and the second parameter is the value you want it replaced with:

```php
<?php

$sql = $query->swapPrefix('ci3_', 'ci4_');
```

#### getStartTime()

Gets the time the query was executed in seconds with microseconds:

```php
<?php

$microtime = $query->getStartTime();
```

#### getDuration()

Returns a float with the duration of the query in seconds with microseconds:

```php
<?php

$microtime = $query->getDuration();
```
