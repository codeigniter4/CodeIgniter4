# Database Forge Class

The Database Forge Class contains methods that help you manage your database.

## Initializing the Forge Class

!!! important "Important"
    In order to initialize the Forge class, your database driver must already be running, since the Forge class relies on it.

Load the Forge Class as follows:

```php
<?php

$forge = \Config\Database::forge();
```

You can also pass another database group name to the DB Forge loader, in case the database you want to manage isn't the default one:

```php
<?php

$this->myforge = \Config\Database::forge('other_db');
```

In the above example, we're passing the name of a different database group to connect to as the first parameter.

## Creating and Dropping Databases

### $forge->createDatabase('db_name')

Permits you to create the database specified in the first parameter. Returns true/false based on success or failure:

```php
<?php

if ($forge->createDatabase('my_db')) {
    echo 'Database created!';
}
```

An optional second parameter set to true will add an `IF NOT EXISTS` statement or will check if a database exists before creating it (depending on DBMS).

```php
<?php

$forge->createDatabase('my_db', true);
/*
 * gives CREATE DATABASE IF NOT EXISTS `my_db`
 * or will check if a database exists
 */
```

### $forge->dropDatabase('db_name')

Permits you to drop the database specified in the first parameter. Returns true/false based on success or failure:

```php
<?php

if ($forge->dropDatabase('my_db')) {
    echo 'Database deleted!';
}
```

### Creating Databases in the Command Line

CodeIgniter supports creating databases straight from your favorite terminal using the dedicated `db:create` command. When using this command, it is assumed that the database does not yet exist. Otherwise, CodeIgniter will complain that the database creation has failed.

To start, just type the command and the name of the database (e.g., `foo`):

```console
php spark db:create foo
```

If everything went fine, you should expect the `Database "foo" successfully created.` message to be displayed.

If you are in a testing environment or you are using the SQLite3 driver, you may pass in the file extension for the file where the database will be created using the `--ext` option. Valid values are `db` and `sqlite`, and the default is `db`. Remember that these should not be preceded by a period:

```console
php spark db:create foo --ext sqlite
```

The above command will create the db file in **WRITEPATH/foo.sqlite**.

!!! note "Note"
    When using the special SQLite3 database name `:memory:`, expect that the command will still produce a success message but no database file is created. This is because SQLite3 will just use an in-memory database.

## Creating Tables

There are several things you may wish to do when creating tables: add fields, add keys to the table, and alter columns. CodeIgniter provides a mechanism for this.

### Adding Fields

#### $forge->addField()

Fields are normally created via an associative array. Within the array, you must include a `type` key that relates to the datatype of the field.

For example, `INT`, `VARCHAR`, `TEXT`, etc. Many datatypes (for example `VARCHAR`) also require a `constraint` key.

```php
<?php

$fields = [
    'users' => [
        'type'       => 'VARCHAR',
        'constraint' => 100,
    ],
];
// will translate to "users VARCHAR(100)" when the field is added.
```

Additionally, the following key/values can be used:

- `unsigned`/true : to generate `UNSIGNED` in the field definition.
- `default`/value : to generate a `DEFAULT` constraint in the field definition.
- `null`/true : to generate `NULL` in the field definition. Without this, the field will default to `NOT NULL`.
- `auto_increment`/true : to generate an auto_increment flag on the field. Note that the field type must be a type that supports this, such as `INTEGER`.
- `unique`/true : to generate a unique key for the field definition.

```php
<?php

$fields = [
    'id' => [
        'type'           => 'INT',
        'constraint'     => 5,
        'unsigned'       => true,
        'auto_increment' => true,
    ],
    'title' => [
        'type'       => 'VARCHAR',
        'constraint' => '100',
        'unique'     => true,
    ],
    'author' => [
        'type'       => 'VARCHAR',
        'constraint' => 100,
        'default'    => 'King of Town',
    ],
    'description' => [
        'type' => 'TEXT',
        'null' => true,
    ],
    'status' => [
        'type'       => 'ENUM',
        'constraint' => ['publish', 'pending', 'draft'],
        'default'    => 'pending',
    ],
];
$forge->addField($fields);
```

After the fields have been defined, they can be added using `$forge->addField($fields)` followed by a call to the [createTable()](#creating-a-table) method.

#### Notes on Data Types

##### Floating-Point Types

Floating-point types such as `FLOAT` and `DOUBLE` represent approximate values. Therefore, they should not be used when exact values are needed.

```
mysql> CREATE TABLE t (f FLOAT, d DOUBLE);
mysql> INSERT INTO t VALUES(99.9, 99.9);

mysql> SELECT * FROM t WHERE f=99.9;
Empty set (0.00 sec)

mysql> SELECT * FROM t WHERE f > 99.89 AND f < 99.91;
+------+------+
| f    | d    |
+------+------+
| 99.9 | 99.9 |
+------+------+
1 row in set (0.01 sec)
```

When it is important to preserve exact precision, for example with monetary data, `DECIMAL` or `NUMERIC` should be used.

##### TEXT

`TEXT` should not be used on SQLSRV. It is deprecated. See [ntext, text, and image (Transact-SQL) - SQL Server | Microsoft Learn](https://learn.microsoft.com/en-us/sql/t-sql/data-types/ntext-text-and-image-transact-sql?view=sql-server-ver16).

##### ENUM

Not all databases support `ENUM`.

Starting with v4.5.0, `SQLSRV` Forge converts `ENUM` data types to `VARCHAR(n)`. Previous versions converted to `TEXT`.

#### Raw Sql Strings as Default Values

!!! success "Available from version 4.2.0"

Since v4.2.0, `$forge->addField()` accepts a `CodeIgniter\Database\RawSql` instance, which expresses raw SQL strings.

```php
<?php

use CodeIgniter\Database\RawSql;

$fields = [
    'id' => [
        'type'           => 'INT',
        'constraint'     => 5,
        'unsigned'       => true,
        'auto_increment' => true,
    ],
    'created_at' => [
        'type'    => 'TIMESTAMP',
        'default' => new RawSql('CURRENT_TIMESTAMP'),
    ],
];
$forge->addField($fields);
/*
gives:
    "id" INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
*/
```

!!! warning "Warning"
    When you use `RawSql`, you MUST escape the data manually. Failure to do so could result in SQL injections.

#### Passing Strings as Fields

If you know exactly how you want a field to be created, you can pass the string into the field definitions with `addField()`:

```php
<?php

$forge->addField("label varchar(100) NOT NULL DEFAULT 'default label'");
```

!!! note "Note"
    Passing raw strings as fields cannot be followed by `addKey()` calls on those fields.

!!! note "Note"
    Multiple calls to `addField()` are cumulative.

#### Creating an id Field

There is a special exception for creating id fields. A field with type id will automatically be assigned as an INT(9) auto_incrementing Primary Key.

```php
<?php

$forge->addField('id');
// gives `id` INT(9) NOT NULL AUTO_INCREMENT
```

### Adding Keys

#### $forge->addKey()

Generally speaking, you'll want your table to have Keys. This is accomplished with `$forge->addKey('field')`. The optional second parameter set to true will make it a primary key and the third parameter set to true will make it a unique key. You may specify a name with the fourth parameter. Note that `addKey()` must be followed by a call to `createTable()` or `processIndexes()` when the table already exists.

Multi-column non-primary keys must be sent as an array. Sample output below is for MySQL.

```php
<?php

$forge->addKey('blog_id', true);
// gives PRIMARY KEY `blog_id` (`blog_id`)

$forge->addKey('blog_id', true);
$forge->addKey('site_id', true);
// gives PRIMARY KEY `blog_id_site_id` (`blog_id`, `site_id`)

$forge->addKey('blog_name');
// gives KEY `blog_name` (`blog_name`)

$forge->addKey(['blog_name', 'blog_label'], false, false, 'my_key_name');
// gives KEY `my_key_name` (`blog_name`, `blog_label`)

$forge->addKey(['blog_id', 'uri'], false, true, 'my_key_name');
// gives UNIQUE KEY `my_key_name` (`blog_id`, `uri`)
```

#### $forge->addPrimaryKey()

Adds a primary key. See [Adding Keys](#adding-keys) for usage.

#### $forge->addUniqueKey()

To make the code easier to read, it is also possible to add primary and unique keys with specific methods:

```php
<?php

$forge->addPrimaryKey('blog_id', 'pd_name');
// gives PRIMARY KEY `pd_name` (`blog_id`)

$forge->addUniqueKey(['blog_id', 'uri'], 'key_name');
// gives UNIQUE KEY `key_name` (`blog_id`, `uri`)
```

!!! note "Note"
    When you add a primary key, MySQL and SQLite will assume the name `PRIMARY` even if a name is provided.

### Adding Foreign Keys

Foreign Keys help to enforce relationships and actions across your tables. For tables that support Foreign Keys, you may add them directly in forge:

```php
<?php

$forge->addForeignKey('users_id', 'users', 'id');
// gives CONSTRAINT `TABLENAME_users_id_foreign` FOREIGN KEY(`users_id`) REFERENCES `users`(`id`)

$forge->addForeignKey(['users_id', 'users_name'], 'users', ['id', 'name']);
// gives CONSTRAINT `TABLENAME_users_id_foreign` FOREIGN KEY(`users_id`, `users_name`) REFERENCES `users`(`id`, `name`)
```

You can specify the desired action for the "on update" and "on delete" properties of the constraint as well as the name:

```php
<?php

$forge->addForeignKey('users_id', 'users', 'id', 'CASCADE', 'CASCADE', 'my_fk_name');
// gives CONSTRAINT `my_fk_name` FOREIGN KEY(`users_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE

$forge->addForeignKey('users_id', 'users', 'id', '', 'CASCADE');
// gives CONSTRAINT `TABLENAME_users_foreign` FOREIGN KEY(`users_id`) REFERENCES `users`(`id`) ON DELETE CASCADE

$forge->addForeignKey(['users_id', 'users_name'], 'users', ['id', 'name'], 'CASCADE', 'CASCADE', 'my_fk_name');
// gives CONSTRAINT `my_fk_name` FOREIGN KEY(`users_id`, `users_name`) REFERENCES `users`(`id`, `name`) ON DELETE CASCADE ON UPDATE CASCADE
```

!!! note "Note"
    SQLite3 does not support the naming of foreign keys. CodeIgniter will refer to them by `prefix_table_column_foreign`.

### Creating a Table

After fields and keys have been declared, you can create a new table with:

```php
<?php

$forge->createTable('table_name');
// gives CREATE TABLE table_name
```

An optional second parameter set to true will create the table only if it doesn't already exist.

```php
<?php

$forge->createTable('table_name', true);
// creates table only if table does not exist
```

You could also pass optional table attributes, such as MySQL's `ENGINE`:

```php
<?php

$attributes = ['ENGINE' => 'InnoDB'];
$forge->createTable('table_name', false, $attributes);
// produces: CREATE TABLE `table_name` (...) ENGINE = InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
```

!!! note "Note"
    Unless you specify the `CHARACTER SET` and/or `COLLATE` attributes, `createTable()` will always add them with your configured *charset* and *DBCollat* values, as long as they are not empty (MySQL only).

## Dropping Tables

### Dropping a Table

Execute a `DROP TABLE` statement and optionally add an `IF EXISTS` clause.

```php
<?php

// Produces: DROP TABLE `table_name`
$forge->dropTable('table_name');

// Produces: DROP TABLE IF EXISTS `table_name`
$forge->dropTable('table_name', true);
```

A third parameter can be passed to add a `CASCADE` option, which might be required for some drivers to handle removal of tables with foreign keys.

```php
<?php

// Produces: DROP TABLE `table_name` CASCADE
$forge->dropTable('table_name', false, true);
```

## Modifying Tables

### Adding a Field to a Table

#### $forge->addColumn()

The `addColumn()` method is used to modify an existing table. It accepts the same field array as [Creating Tables](#adding-fields), and can be used to add additional fields.

!!! note "Note"
    Unlike when creating a table, if `null` is not specified, the column will be `NULL`, not `NOT NULL`.

```php
<?php

$fields = [
    'preferences' => ['type' => 'TEXT'],
];
$forge->addColumn('table_name', $fields);
// Executes: ALTER TABLE `table_name` ADD `preferences` TEXT
```

If you are using MySQL or CUBRID, then you can take advantage of their `AFTER` and `FIRST` clauses to position the new column.

Examples:

```php
<?php

// Will place the new column after the `another_field` column:
$fields = [
    'preferences' => ['type' => 'TEXT', 'after' => 'another_field'],
];

// Will place the new column at the start of the table definition:
$fields = [
    'preferences' => ['type' => 'TEXT', 'first' => true],
];
```

### Dropping Fields From a Table

#### $forge->dropColumn()

Used to remove a column from a table.

```php
<?php

$forge->dropColumn('table_name', 'column_to_drop'); // to drop one single column
```

Used to remove multiple columns from a table.

```php
<?php

$forge->dropColumn('table_name', 'column_1,column_2');      // by proving comma separated column names
$forge->dropColumn('table_name', ['column_1', 'column_2']); // by proving array of column names
```

### Modifying a Field in a Table

#### $forge->modifyColumn()

The usage of this method is identical to `addColumn()`, except it alters an existing column rather than adding a new one. In order to change the name, you can add a "name" key into the field defining array.

```php
<?php

$fields = [
    'old_name' => [
        'name' => 'new_name',
        'type' => 'TEXT',
        'null' => false,
    ],
];
$forge->modifyColumn('table_name', $fields);
// gives ALTER TABLE `table_name` CHANGE `old_name` `new_name` TEXT NOT NULL
```

!!! note "Note"
    The `modifyColumn()` method may unexpectedly change `NULL`/`NOT NULL`. So it is recommended to always specify the value for the `null` key. Unlike when creating a table, if `null` is not specified, the column will be `NULL`, not `NOT NULL`.

!!! note "Note"
    Due to a bug, prior to v4.3.4, SQLite3 may not set `NOT NULL` even if you specify `'null' => false`.

!!! note "Note"
    Due to a bug, prior to v4.3.4, Postgres and SQLSRV set `NOT NULL` even if you specify `'null' => true`.

### Adding Keys to a Table

!!! success "Available from version 4.3.0"

You may add keys to an existing table by using `addKey()`, `addPrimaryKey()`, `addUniqueKey()` or `addForeignKey()` and `processIndexes()`:

```php
<?php

$this->forge->addKey(['category', 'name'], false, false, 'category_name');
$this->forge->addPrimaryKey('id', 'pk_actions');
$this->forge->addForeignKey('userid', 'user', 'id', '', '', 'userid_fk');
$this->forge->processIndexes('actions');
/* gives:
ALTER TABLE `actions` ADD KEY `category_name` (`category`, `name`);
ALTER TABLE `actions` ADD CONSTRAINT `pk_actions` PRIMARY KEY(`id`);
ALTER TABLE `actions` ADD CONSTRAINT `userid_fk` FOREIGN KEY (`userid`) REFERENCES `user`(`id`);
*/
```

### Dropping a Primary Key

!!! success "Available from version 4.3.0"

Execute a DROP PRIMARY KEY.

```php
<?php

// MySQLi Produces: ALTER TABLE `tablename` DROP PRIMARY KEY
// Others Produces: ALTER TABLE `tablename` DROP CONSTRAINT `pk_tablename`
$forge->dropPrimaryKey('tablename');
```

### Dropping a Key

Execute a DROP KEY.

```php
<?php

// For Indexes Produces:        DROP INDEX `users_index` ON `tablename`
// For Unique Indexes Produces: ALTER TABLE `tablename` DROP CONSTRAINT `users_index`
$forge->dropKey('tablename', 'users_index', false);
```

### Dropping a Foreign Key

Execute a DROP FOREIGN KEY.

```php
<?php

// Produces: ALTER TABLE `tablename` DROP FOREIGN KEY `users_foreign`
$forge->dropForeignKey('tablename', 'users_foreign');
```

### Renaming a Table

Executes a TABLE rename.

```php
<?php

$forge->renameTable('old_table_name', 'new_table_name');
// gives ALTER TABLE `old_table_name` RENAME TO `new_table_name`
```

## Class Reference

## CodeIgniter\Database

### Forge

#### addColumn($table[, $field = []])

- **Parameters**  
    - **$table** `string` Table name to add the column to
    - **$field** `array` Column definition(s)

- **Returns**: true on success, false on failure

- **Return type**: `bool`

Adds a column to an existing table. Usage: See [Adding a Field to a Table](#adding-a-field-to-a-table).

#### addField($field)

- **Parameters**  
    - **$field** `array` Field definition to add

- **Returns**: `\CodeIgniter\Database\Forge` instance (method chaining)

- **Return type**: `\CodeIgniter\Database\Forge`

Adds a field to the set that will be used to create a table. Usage: See [Adding Fields](#adding-fields).

#### addForeignKey($fieldName, $tableName, $tableField[, $onUpdate = '', $onDelete = '', $fkName = ''])

- **Parameters**  
    - string|string[] `$fieldName` Name of a key field or an array of fields
    - **$tableName** `string` Name of a parent table
    - string|string[] `$tableField` Name of a parent table field or an array of fields
    - **$onUpdate** `string` Desired action for the "on update"
    - **$onDelete** `string` Desired action for the "on delete"
    - **$fkName** `string` Name of foreign key. This does not work with SQLite3

- **Returns**: `\CodeIgniter\Database\Forge` instance (method chaining)

- **Return type**: `\CodeIgniter\Database\Forge`

Adds a foreign key to the set that will be used to create a table. Usage: See [Adding Foreign Keys](#adding-foreign-keys).

!!! note "Note"
    `$fkName` can be used since v4.3.0.

#### addKey($key[, $primary = false[, $unique = false[, $keyName = '']]])

- **Parameters**  
    - **$key** `mixed` Name of a key field or an array of fields
    - **$primary** `bool` Set to true if it should be a primary key or a regular one
    - **$unique** `bool` Set to true if it should be a unique key or a regular one
    - **$keyName** `string` Name of key to be added

- **Returns**: `\CodeIgniter\Database\Forge` instance (method chaining)

- **Return type**: `\CodeIgniter\Database\Forge`

Adds a key to the set that will be used to create a table. Usage: See [Adding Keys](#adding-keys).

!!! note "Note"
    `$keyName` can be used since v4.3.0.

#### addPrimaryKey($key[, $keyName = ''])

- **Parameters**  
    - **$key** `mixed` Name of a key field or an array of fields
    - **$keyName** `string` Name of key to be added

- **Returns**: `\CodeIgniter\Database\Forge` instance (method chaining)

- **Return type**: `\CodeIgniter\Database\Forge`

Adds a primary key to the set that will be used to create a table. Usage: See [Adding Keys](#adding-keys).

!!! note "Note"
    `$keyName` can be used since v4.3.0.

#### addUniqueKey($key[, $keyName = ''])

- **Parameters**  
    - **$key** `mixed` Name of a key field or an array of fields
    - **$keyName** `string` Name of key to be added

- **Returns**: `\CodeIgniter\Database\Forge` instance (method chaining)

- **Return type**: `\CodeIgniter\Database\Forge`

Adds a unique key to the set that will be used to create a table. Usage: See [Adding Keys](#adding-keys).

!!! note "Note"
    `$keyName` can be used since v4.3.0.

#### createDatabase($dbName[, $ifNotExists = false])

- **Parameters**  
    - **$dbName** `string` Name of the database to create
    - **$ifNotExists** `bool` Set to true to add an `IF NOT EXISTS` clause or check if database exists

- **Returns**: true on success, false on failure

- **Return type**: `bool`

Creates a new database. Usage: See [Creating and Dropping Databases](#creating-and-dropping-databases).

#### createTable($table[, $ifNotExists = false[, array $attributes = []]])

- **Parameters**  
    - **$table** `string` Name of the table to create
    - **$ifNotExists** `bool` Set to true to add an `IF NOT EXISTS` clause
    - **$attributes** `array` An associative array of table attributes

- **Returns**: Query object on success, false on failure

- **Return type**: `mixed`

Creates a new table. Usage: See [Creating a Table](#creating-a-table).

#### dropColumn($table, $columnNames)

- **Parameters**  
    - **$table** `string` Table name
    - **$columnNames** `mixed` Comma-delimited string or an array of column names

- **Returns**: true on success, false on failure

- **Return type**: `bool`

Drops single or multiple columns from a table. Usage: See [Dropping Fields From a Table](#dropping-fields-from-a-table).

#### dropDatabase($dbName)

- **Parameters**  
    - **$dbName** `string` Name of the database to drop

- **Returns**: true on success, false on failure

- **Return type**: `bool`

Drops a database. Usage: See [Creating and Dropping Databases](#creating-and-dropping-databases).

#### dropKey($table, $keyName[, $prefixKeyName = true])

- **Parameters**  
    - **$table** `string` Name of table that has key
    - **$keyName** `string` Name of key to be dropped
    - **$prefixKeyName** `bool` If database prefix should be added to `$keyName`

- **Returns**: true on success, false on failure

- **Return type**: `bool`

Drops an index or unique index.

!!! note "Note"
    `$keyName` and `$prefixKeyName` can be used since v4.3.0.

#### dropPrimaryKey($table[, $keyName = ''])

- **Parameters**  
    - **$table** `string` Name of table to drop primary key
    - **$keyName** `string` Name of primary key to be dropped

- **Returns**: true on success, false on failure

- **Return type**: `bool`

Drops a primary key from a table.

!!! note "Note"
    `$keyName` can be used since v4.3.0.

#### dropTable($tableName[, $ifExists = false[, $cascade = false]])

- **Parameters**  
    - **$tableName** `string` Name of the table to drop
    - **$ifExists** `bool` Set to true to add an `IF EXISTS` clause
    - **$cascade** `bool` Whether to cascade the drop to dependent objects where supported

- **Returns**: true on success, false on failure

- **Return type**: `bool`

Drops a table. Usage: See [Dropping a Table](#dropping-a-table).

#### processIndexes($table)

!!! success "Available from version 4.3.0"

- **Parameters**  
    - **$table** `string` Name of the table to add indexes to

- **Returns**: true on success, false on failure

- **Return type**: `bool`

Used following `addKey()`, `addPrimaryKey()`, `addUniqueKey()`, and `addForeignKey()` to add indexes to an existing table. See [Adding Keys to a Table](#adding-keys-to-a-table).

#### modifyColumn($table, $field)

- **Parameters**  
    - **$table** `string` Table name
    - **$field** `array` Column definition(s)

- **Returns**: true on success, false on failure

- **Return type**: `bool`

Modifies a table column. Usage: See [Modifying a Field in a Table](#modifying-a-field-in-a-table).

#### renameTable($tableName, $newTableName)

- **Parameters**  
    - **$tableName** `string` Current name of the table
    - **$newTableName** `string` New name of the table

- **Returns**: Query object on success, false on failure

- **Return type**: `mixed`

Renames a table. Usage: See [Renaming a Table](#renaming-a-table).
