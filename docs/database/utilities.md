# Database Utilities

The Database Utility Class contains methods that help you manage your database.

## Initializing the Utility Class

Load the Utility Class as follows:

```php
$dbutil = \Config\Database::utils();
```

You can also pass another database group to the DB Utility loader, in case the database you want to manage isn't the default one:

```php
$dbutil = \Config\Database::utils('group_name');
```

In the above example, we're passing a database group name as the first parameter.

## Using the Database Utilities

### Retrieve List of Database Names

Returns an array of database names:

```php
$dbutil = \Config\Database::utils();

$dbs = $dbutil->listDatabases();

foreach ($dbs as $db) {
    echo $db;
}
```

### Determine If a Database Exists

Sometimes it's helpful to know whether a particular database exists. Returns a boolean `true`/`false`. Usage example:

```php
$dbutil = \Config\Database::utils();

if ($dbutil->databaseExists('database_name')) {
    // some code...
}
```

!!! note "Note"
    Replace `database_name` with the name of the database you are looking for. This method is case sensitive.

### Optimize a Table

Permits you to optimize a table using the table name specified in the first parameter. Returns `true`/`false` based on success or failure:

```php
$dbutil = \Config\Database::utils();

if ($dbutil->optimizeTable('table_name')) {
    echo 'Success!';
}
```

!!! note "Note"
    Not all database platforms support table optimization. It is mostly for use with MySQL.

### Optimize a Database

Permits you to optimize the database your DB class is currently connected to. Returns an array containing the DB status messages or `false` on failure:

```php
$dbutil = \Config\Database::utils();

$result = $dbutil->optimizeDatabase();

if ($result !== false) {
    print_r($result);
}
```

!!! note "Note"
    Not all database platforms support database optimization. It is mostly for use with MySQL.

### Export a Query Result as a CSV File

Permits you to generate a CSV file from a query result. The first parameter of the method must contain the result object from your query. Example:

```php
$db     = db_connect();
$dbutil = \Config\Database::utils();

$query = $db->query('SELECT * FROM mytable');

echo $dbutil->getCSVFromResult($query);
```

The second, third, and fourth parameters allow you to set the delimiter, newline, and enclosure characters respectively. By default, commas are used as the delimiter, `"\n"` is used as a new line, and a double-quote is used as the enclosure. Example:

```php
$db     = db_connect();
$dbutil = \Config\Database::utils();

$query = $db->query('SELECT * FROM mytable');

$delimiter = ',';
$newline   = "\r\n";
$enclosure = '"';

echo $dbutil->getCSVFromResult($query, $delimiter, $newline, $enclosure);
```

!!! important "Important"
    This method will NOT write the CSV file for you. It simply creates the CSV layout. If you need to write the file, use the `write_file()` helper.

### Export a Query Result as an XML Document

Permits you to generate an XML file from a query result. The first parameter expects a query result object, and the second may contain an optional array of config parameters. Example:

```php
<?php

$db     = db_connect();
$dbutil = \Config\Database::utils();

$query = $db->query('SELECT * FROM mytable');

$config = [
    'root'    => 'root',
    'element' => 'element',
    'newline' => "\n",
    'tab'     => "\t",
];

echo $dbutil->getXMLFromResult($query, $config);
```

It will produce the following XML result when the `mytable` table has columns `id` and `name`:

```
<root>
    <element>
        <id>1</id>
        <name>bar</name>
    </element>
</root>
```

!!! important "Important"
    This method will NOT write the XML file for you. It simply creates the XML layout. If you need to write the file, use the `write_file()` helper.
