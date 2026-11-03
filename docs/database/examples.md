# Quick Start: Usage Examples

The following page contains example code showing how the database class is used. For complete details please read the individual pages describing each function.

!!! note "Note"
    CodeIgniter doesn't support dots (`.`) in the table and column names. Since v4.5.0, database names with dots are supported.

## Initializing the Database Class

The following code loads and initializes the database class based on your [configuration](configuration.md) settings:

```php
<?php

$db = \Config\Database::connect();
```

Once loaded, the class is ready to be used as described below.

!!! note "Note"
    If all your controllers require database access, you can connect in your base controller's `initController()` method. See [Connecting to a Database](connecting.md) for connection examples.

## Standard Query With Multiple Results (Object Version)

```php
<?php

$query   = $db->query('SELECT name, title, email FROM my_table');
$results = $query->getResult();

foreach ($results as $row) {
    echo $row->title;
    echo $row->name;
    echo $row->email;
}

echo 'Total Results: ' . count($results);
```

The above `getResult()` function returns an array of **objects**. Example: `$row->title`

## Standard Query With Multiple Results (Array Version)

```php
<?php

$query   = $db->query('SELECT name, title, email FROM my_table');
$results = $query->getResultArray();

foreach ($results as $row) {
    echo $row['title'];
    echo $row['name'];
    echo $row['email'];
}
```

The above `getResultArray()` function returns an array of associative arrays. Example: `$row['title']`

## Standard Query With Single Result

```php
<?php

$query = $db->query('SELECT name FROM my_table LIMIT 1');
$row   = $query->getRow();
echo $row->name;
```

The above `getRow()` function returns an **object**. Example: `$row->name`

## Standard Query With Single Result (Array version)

```php
<?php

$query = $db->query('SELECT name FROM my_table LIMIT 1');
$row   = $query->getRowArray();
echo $row['name'];
```

The above `getRowArray()` function returns an **array**. Example: `$row['name']`.

## Standard Insert

```php
<?php

$sql = 'INSERT INTO mytable (title, name) VALUES (' . $db->escape($title) . ', ' . $db->escape($name) . ')';
$db->query($sql);
echo $db->affectedRows();
```

## Query Builder Query

The [Query Builder](query_builder.md) gives you a simplified means of retrieving data:

```php
<?php

$query = $db->table('table_name')->get();

foreach ($query->getResult() as $row) {
    echo $row->title;
}
```

The above `get()` function retrieves all the results from the supplied table. The [Query Builder](query_builder.md) class contains a full complement of functions for working with data.

## Query Builder Insert

```php
<?php

$data = [
    'title' => $title,
    'name'  => $name,
    'date'  => $date,
];

$db->table('mytable')->insert($data);
// Produces: INSERT INTO mytable (title, name, date) VALUES ('{$title}', '{$name}', '{$date}')
```
