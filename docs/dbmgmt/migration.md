# Database Migrations

Migrations are a convenient way for you to alter your database in a structured and organized manner. You could edit fragments of SQL by hand but you would then be responsible for telling other developers that they need to go and run them. You would also have to keep track of which changes need to be run against the production machines next time you deploy.

The database table **migrations** tracks which migrations have already been run, so all you have to do is make sure your migrations are in place and run the `spark migrate` command to bring the database up to the most recent state. You can also use `spark migrate --all` to include migrations from all namespaces.

## Migration File Names

A migration file name is made up of a timestamp prefix, an underscore (`_`), and a descriptive name (classname).

- **2024-09-08-013653_AddBlogTable.php**

Each migration is numbered using the timestamp (**2024-09-08-013653**) when the migration was created, in **YYYY-MM-DD-HHIISS** format.

The descriptive name (**AddBlogTable**) for the migration is a classname in PHP. So you must use a valid classname.

The year, month, day, and time in a prefix can be separated from each other by dashes (`-`), underscores (`_`), or not at all. For example:

- **2012-10-31-100538_AlterBlogTrackViews.php**
- **2012_10_31_100539_AlterBlogAddTranslations.php**
- **20121031100537_AddBlog.php**

Each migration is run in numeric order forward or backwards depending on the method taken. This helps prevent numbering conflicts when working in a team environment.

## Create a Migration

This will be the first migration for a new site which has a blog. All migrations go in the **app/Database/Migrations/** directory and have names such as **2022-01-31-013057_AddBlog.php**.

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBlog extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'blog_id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'blog_title' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'blog_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('blog_id', true);
        $this->forge->createTable('blog');
    }

    public function down()
    {
        $this->forge->dropTable('blog');
    }
}
```

The database connection and the database Forge class are both available to you through `$this->db` and `$this->forge`, respectively.

Alternatively, you can use a command-line call to generate a skeleton migration file. See [make:migration](#makemigration) in [Command-Line Tools](#command-line-tools) for more details.

!!! note "Note"
    Since the migration class is a PHP class, the classname must be unique in every migration file.

### Foreign Keys

When your tables include Foreign Keys, migrations can often cause problems as you attempt to drop tables and columns. To temporarily bypass the foreign key checks while running migrations, use the `disableForeignKeyChecks()` and `enableForeignKeyChecks()` methods on the database connection.

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBlog extends Migration
{
    public function up()
    {
        $this->db->disableForeignKeyChecks();

        // Migration rules would go here..

        $this->db->enableForeignKeyChecks();
    }
}
```

### Database Groups

A migration will only be run against a single database group. If you have multiple groups defined in **app/Config/Database.php**, then by default it will run against the `$defaultGroup` as specified in that same configuration file.

There may be times when you need different schemas for different database groups. Perhaps you have one database that is used for all general site information, while another database is used for mission-critical data.

You can ensure that migrations are run only against the proper group by setting the `$DBGroup` property on your migration. This name must match the name of the database group exactly:

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBlog extends Migration
{
    protected $DBGroup = 'alternate_db_group';

    public function up()
    {
        // ...
    }

    public function down()
    {
        // ...
    }
}
```

!!! note "Note"
    The **migrations** table that tracks which migrations have already been run will always be created in the default database group.

### Namespaces

The migration library can automatically scan all namespaces you have defined within **app/Config/Autoload.php** or loaded from an external source like Composer, using the `$psr4` property for matching directory names. It will include all migrations it finds in **Database/Migrations**.

Each namespace has its own version sequence, which will help you upgrade and downgrade each module (namespace) without affecting other namespaces.

For example, assume that we have the following namespaces defined in our Autoload configuration file:

```php
$psr4 = [
    APP_NAMESPACE => APPPATH,
    'MyCompany'   => ROOTPATH . 'MyCompany',
];
```

This will look for any migrations located at both **APPPATH/Database/Migrations** and **ROOTPATH/MyCompany/Database/Migrations**. This makes it simple to include migrations in your re-usable, modular code suites.

## Command-Line Tools

CodeIgniter ships with several [commands](../cli/spark_commands.md) that are available from the command line to help you work with migrations. These tools make things easier for those of you that wish to use them. The tools primarily provide access to the same methods that are available within the MigrationRunner class.

### migrate

Migrates a database group with all available migrations:

```console
php spark migrate
```

You can use `migrate` with the following options:

- `-g` - to specify a database group. If specified, only migrations for the specified database group will be run. If not specified, all migrations will be run.
- `-n` - to choose a namespace, otherwise the `App` namespace will be used.
- `--all` - to migrate all namespaces to the latest migration.

This example will migrate the `Acme\Blog` namespace with any new migrations on the test database group:

For Unix:

```console
php spark migrate -g test -n Acme\\Blog
```

For Windows:

```console
php spark migrate -g test -n Acme\Blog
```

When using the `--all` option, it will scan through all namespaces attempting to find any migrations that have not been run. These will all be collected and then sorted as a group by date created. This should help to minimize any potential conflicts between the main application and any modules.

### migrate:rollback

Rolls back all migrations to a blank slate, effectively migration 0:

```console
php spark migrate:rollback
```

You can use `migrate:rollback` with the following options:

- `-b` - to choose a batch: natural numbers specify the batch.
- `-f` - to bypass the confirmation question, which is only asked in a production environment.

### migrate:refresh

Refreshes the database state by first rolling back all migrations, and then migrating all:

```console
php spark migrate:refresh
```

You can use `migrate:refresh` with the following options:

- `-g` - to specify a database group. If specified, only migrations for the specified database group will be run. If not specified, all migrations will be run.
- `-n` - to choose a namespace, otherwise the `App` namespace will be used.
- `--all` - to refresh all namespaces.
- `-f` - to bypass the confirmation question, which is only asked in a production environment.

### migrate:status

Displays a list of all migrations and the date and time they ran, or '--' if they have not been run:

```console
php spark migrate:status

...

+----------------------+-------------------+-----------------------+---------+---------------------+-------+
| Namespace            | Version           | Filename              | Group   | Migrated On         | Batch |
+----------------------+-------------------+-----------------------+---------+---------------------+-------+
| App                  | 2022-04-06-234508 | CreateCiSessionsTable | default | 2022-04-06 18:45:14 | 2     |
| CodeIgniter\Settings | 2021-07-04-041948 | CreateSettingsTable   | default | 2022-04-06 01:23:08 | 1     |
| CodeIgniter\Settings | 2021-11-14-143905 | AddContextColumn      | default | 2022-04-06 01:23:08 | 1     |
+----------------------+-------------------+-----------------------+---------+---------------------+-------+
```

You can use `migrate:status` with the following options:

- `-g` - to specify a database group. If specified, only migrations for the specified database group will be checked. If not specified, all migrations will be checked.

### make:migration

Creates a skeleton migration file in **app/Database/Migrations**. It automatically prepends the current timestamp. The class name it creates is the Pascal case version of the filename.

```console
php spark make:migration <class> [options]
```

You can use `make:migration` with the following options:

- `--namespace` - Set root namespace. Default: `APP_NAMESPACE`.
- `--suffix` - Append the component title to the class name.

The following options are also available to generate the migration file for database sessions:

- `--session` - Generates the migration file for database sessions.
- `--table` - Table name to use for database sessions. Default: `ci_sessions`.
- `--dbgroup` - Database group to use for database sessions. Default: `default`.

## Migration Preferences

The following is a table of all the config options for migrations, available in **app/Config/Migrations.php**.

| Preference | Default | Options | Description |
|----|----|----|----|
| **enabled** | true | true / false | Enable or disable migrations. |
| **table** | migrations | None | The table name for storing the schema version number. This table is always created in the default database group (`$defaultGroup`). |
| **timestampFormat** | Y-m-d-His_ |  | The format to use for timestamps when creating a migration. |
| **lock** | false | true / false | Enable distributed locking to prevent concurrent migrations in multi-process environments (e.g., Kubernetes). |

## Class Reference

## CodeIgniter\Database

### MigrationRunner

#### findMigrations()

- **Returns**: An array of migration files
- **Return type**: `array`

An array of migration filenames found in the `path` property is returned.

#### latest($group)

- **Parameters**  
    - **$group** `mixed` database group name, if null default database group will be used.

- **Returns**: `true` on success, `false` on failure

- **Return type**: `bool`

This locates migrations for a namespace (or all namespaces), determines which migrations have not yet been run, and runs them in order of their version (namespaces intermingled).

#### regress($targetBatch, $group)

- **Parameters**  
    - **$targetBatch** `int` previous batch to migrate down to; 1+ specifies the batch, 0 reverts all, negative refers to the relative batch (e.g., -3 means "three batches back")
    - ?string `$group` database group name, if null default database group will be used.

- **Returns**: `true` on success, `false` on failure or if no migrations are found

- **Return type**: `bool`

Regress can be used to roll back changes to a previous state, batch by batch.

```php
<?php

$migration->regress(5);
$migration->regress(-1);
```

#### force($path, $namespace, $group)

- **Parameters**  
    - **$path** `mixed` path to a valid migration file.
    - **$namespace** `mixed` namespace of the provided migration.
    - **$group** `mixed` database group name, if null default database group will be used.

- **Returns**: `true` on success, `false` on failure

- **Return type**: `bool`

This forces a single file to migrate regardless of order or batches. The `up()` or `down()` method is detected based on whether it has already been migrated.

!!! note "Note"
    This method is recommended only for testing and could cause data consistency issues.

#### setNamespace($namespace)

- **Parameters**  
    - **$namespace** `string|null` application namespace. `null` is all namespaces.

- **Returns**: The current MigrationRunner instance

- **Return type**: `CodeIgniter\Database\MigrationRunner`

Sets the namespace in which the library should look for migration files:

```php
<?php

$migration->setNamespace($namespace)->latest();
```

!!! note "Note"
    If you set `null`, it looks for migration files in all namespaces.

#### setGroup($group)

- **Parameters**  
    - **$group** `string` database group name.

- **Returns**: The current MigrationRunner instance

- **Return type**: `CodeIgniter\Database\MigrationRunner`

Sets the group in which the library should look for migration files:

```php
<?php

$migration->setGroup($group)->latest();
```
