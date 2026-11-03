# Query Helper Methods

## Information From Executing a Query

### $db->insertID()

The insert ID number when performing database inserts.

### $db->affectedRows()

Displays the number of affected rows, when doing "write" type queries (insert, update, etc.).

!!! note "Note"
    In MySQL, "DELETE FROM TABLE" returns 0 affected rows. The database class has a small hack that allows it to return the correct number of affected rows. By default, this hack is enabled, but it can be turned off in the database driver file.

### $db->getLastQuery()

Returns a Query object that represents the last query that was run (the query string, not the result).

## Information About Your Database

### $builder->countAll()

Permits you to determine the number of rows in a particular table. Select the table with `$db->table()` first. The optional boolean parameter controls whether to reset the Query Builder state.

```php
<?php

echo $db->table('my_table')->countAll();
// Produces an integer, like 25
```

### $builder->countAllResults()

Permits you to determine the number of rows in a particular result set. Select the table with `$db->table()` first. The optional boolean parameter controls whether to reset the Query Builder state.

```php
<?php

echo $db->table('my_table')->like('title', 'match')->countAllResults();
// Produces an integer, like 5
```

### $db->getPlatform()

Outputs the database platform (DBDriver) you are running (MySQLi, SQLSRV, Postgre, etc.):

```php
<?php

echo $db->getPlatform();
```

### $db->getVersion()

Outputs the database version you are running:

```php
<?php

echo $db->getVersion();
```
