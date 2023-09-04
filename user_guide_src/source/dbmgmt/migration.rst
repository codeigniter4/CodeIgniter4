###################
Database Migrations
###################

Migrations are a convenient way for you to alter your database in a
structured and organized manner. You could edit fragments of SQL by hand
but you would then be responsible for telling other developers that they
need to go and run them. You would also have to keep track of which changes
need to be run against the production machines next time you deploy.

The database table **migrations** tracks which migrations have already been
run, so all you have to do is make sure your migrations are in place and
run the ``spark migrate`` command to bring the database up to the most recent
state. You can also use ``spark migrate --all`` to
include migrations from all namespaces.

.. contents::
    :local:
    :depth: 2

********************
Migration File Names
********************

Each Migration is run in numeric order forward or backwards depending on the
method taken. Each migration is numbered using the timestamp when the migration
was created, in **YYYY-MM-DD-HHIISS** format (e.g., **2012-10-31-100537**). This
helps prevent numbering conflicts when working in a team environment.

Prefix your migration files with the migration number followed by an underscore
and a descriptive name for the migration. The year, month, and date can be separated
from each other by dashes, underscores, or not at all. For example:

* 2012-10-31-100538_AlterBlogTrackViews.php
* 2012_10_31_100539_AlterBlogAddTranslations.php
* 20121031100537_AddBlog.php

******************
Create a Migration
******************

This will be the first migration for a new site which has a blog. All
migrations go in the **app/Database/Migrations/** directory and have names such
as **2022-01-31-013057_AddBlog.php**.

.. literalinclude:: migration/001.php

The database connection and the database Forge class are both available to you through
``$this->db`` and ``$this->forge``, respectively.

Alternatively, you can use a command-line call to generate a skeleton migration file.
See **make:migration** in :ref:`command-line-tools` for more details.

.. note:: Since the migration class is a PHP class, the classname must be unique in every migration file.

Foreign Keys
============

When your tables include Foreign Keys, migrations can often cause problems as you attempt to drop tables and columns.
To temporarily bypass the foreign key checks while running migrations, use the ``disableForeignKeyChecks()`` and
``enableForeignKeyChecks()`` methods on the database connection.

.. literalinclude:: migration/002.php

Database Groups
===============

A migration will only be run against a single database group. If you have multiple groups defined in
**app/Config/Database.php**, then it will run against the ``$defaultGroup`` as specified
in that same configuration file.

There may be times when you need different schemas for different
database groups. Perhaps you have one database that is used for all general site information, while
another database is used for mission critical data.

You can ensure that migrations are run only
against the proper group by setting the ``$DBGroup`` property on your migration. This name must
match the name of the database group exactly:

.. literalinclude:: migration/003.php

Namespaces
==========

The migration library can automatically scan all namespaces you have defined within
**app/Config/Autoload.php** or loaded from an external source like Composer, using
the ``$psr4`` property for matching directory names. It will include all migrations
it finds in **Database/Migrations**.

Each namespace has its own version sequence, this will help you upgrade and downgrade each module (namespace) without affecting other namespaces.

For example, assume that we have the following namespaces defined in our Autoload
configuration file:

.. literalinclude:: migration/004.php

This will look for any migrations located at both **APPPATH/Database/Migrations** and
**ROOTPATH/MyCompany/Database/Migrations**. This makes it simple to include migrations in your
re-usable, modular code suites.

.. _command-line-tools:

*******************
Command-Line Tools
*******************

CodeIgniter ships with several :doc:`commands </cli/spark_commands>` that are available from the command line to help
you work with migrations. These tools make things easier for those of you
that wish to use them. The tools primarily provide access to the same methods that are available within the MigrationRunner class.

migrate
=======

Migrates a database group with all available migrations:

.. code-block:: console

    php spark migrate

You can use (migrate) with the following options:

- ``-g`` - to specify database group. If specified, only migrations for the specified database group will be run. If not specified, all migrations will be run.
- ``-n`` - to choose namespace, otherwise ``App`` namespace will be used.
- ``--all`` - to migrate all namespaces to the latest migration.

This example will migrate ``Acme\Blog`` namespace with any new migrations on the test database group:

For Unix:

.. code-block:: console

    php spark migrate -g test -n Acme\\Blog

For Windows:

.. code-block:: console

    php spark migrate -g test -n Acme\Blog

When using the ``--all`` option, it will scan through all namespaces attempting to find any migrations that have
not been run. These will all be collected and then sorted as a group by date created. This should help
to minimize any potential conflicts between the main application and any modules.

rollback
========

Rolls back all migrations to a blank slate, effectively migration 0:

.. code-block:: console

  php spark migrate:rollback

You can use (rollback) with the following options:

- ``-b`` - to choose a batch: natural numbers specify the batch.
- ``-f`` - to force a bypass confirmation question, it is only asked in a production environment.

refresh
=======

Refreshes the database state by first rolling back all migrations, and then migrating all:

.. code-block:: console

  php spark migrate:refresh

You can use (refresh) with the following options:

- ``-g`` - to specify database group. If specified, only migrations for the specified database group will be run. If not specified, all migrations will be run.
- ``-n`` - to choose namespace, otherwise ``App`` namespace will be used.
- ``--all`` - to refresh all namespaces.
- ``-f`` - to force a bypass confirmation question, it is only asked in a production environment.

status
======

Displays a list of all migrations and the date and time they ran, or '--' if they have not been run:

.. code-block:: console

  php spark migrate:status

  ...

  +----------------------+-------------------+-----------------------+---------+---------------------+-------+
  | Namespace            | Version           | Filename              | Group   | Migrated On         | Batch |
  +----------------------+-------------------+-----------------------+---------+---------------------+-------+
  | App                  | 2022-04-06-234508 | CreateCiSessionsTable | default | 2022-04-06 18:45:14 | 2     |
  | CodeIgniter\Settings | 2021-07-04-041948 | CreateSettingsTable   | default | 2022-04-06 01:23:08 | 1     |
  | CodeIgniter\Settings | 2021-11-14-143905 | AddContextColumn      | default | 2022-04-06 01:23:08 | 1     |
  +----------------------+-------------------+-----------------------+---------+---------------------+-------+

You can use (status) with the following options:

- ``-g`` - to specify database group. If specified, only migrations for the specified database group will be checked. If not specified, all migrations will be checked.

make:migration
==============

Creates a skeleton migration file in **app/Database/Migrations**.
It automatically prepends the current timestamp. The class name it
creates is the Pascal case version of the filename.

.. code-block:: console

  php spark make:migration <class> [options]

You can use (``make:migration``) with the following options:

- ``--namespace`` - Set root namespace. Default: ``APP_NAMESPACE``.
- ``--suffix``    - Append the component title to the class name.

The following options are also available to generate the migration file for
database sessions:

- ``--session``   - Generates the migration file for database sessions.
- ``--table``     - Table name to use for database sessions. Default: ``ci_sessions``.
- ``--dbgroup``   - Database group to use for database sessions. Default: ``default``.

*********************
Migration Preferences
*********************

The following is a table of all the config options for migrations, available in **app/Config/Migrations.php**.

==================== ============ ============= =============================================================
Preference           Default      Options       Description
==================== ============ ============= =============================================================
**enabled**          true         true / false  Enable or disable migrations.
**table**            migrations   None          The table name for storing the schema version number. This
                                                table is always created in the default database group
                                                (``$defaultGroup``).
**timestampFormat**  Y-m-d-His\_                The format to use for timestamps when creating a migration.
==================== ============ ============= =============================================================

***************
Class Reference
***************

.. php:namespace:: CodeIgniter\Database

.. php:class:: MigrationRunner

    .. php:method:: findMigrations()

        :returns:    An array of migration files
        :rtype:    array

        An array of migration filenames are returned that are found in the ``path`` property.

    .. php:method:: latest($group)

        :param    mixed    $group: database group name, if null default database group will be used.
        :returns:    ``true`` on success, ``false`` on failure
        :rtype:    bool

        This locates migrations for a namespace (or all namespaces), determines which migrations
        have not yet been run, and runs them in order of their version (namespaces intermingled).

    .. php:method:: regress($targetBatch, $group)

        :param    int    $targetBatch: previous batch to migrate down to; 1+ specifies the batch, 0 reverts all, negative refers to the relative batch (e.g., -3 means "three batches back")
        :param    ?string    $group: database group name, if null default database group will be used.
        :returns:    ``true`` on success, ``false`` on failure or no migrations are found
        :rtype:    bool

        Regress can be used to roll back changes to a previous state, batch by batch.

        .. literalinclude:: migration/006.php

    .. php:method:: force($path, $namespace, $group)

        :param    mixed    $path:  path to a valid migration file.
        :param    mixed    $namespace: namespace of the provided migration.
        :param    mixed    $group: database group name, if null default database group will be used.
        :returns:    ``true`` on success, ``false`` on failure
        :rtype:    bool

        This forces a single file to migrate regardless of order or batches. Method ``up()`` or ``down()`` is detected based on whether it has already been migrated.

        .. note:: This method is recommended only for testing and could cause data consistency issues.

    .. php:method:: setNamespace($namespace)

        :param  string|null  $namespace: application namespace. ``null`` is all namespaces.
        :returns:   The current MigrationRunner instance
        :rtype:     CodeIgniter\\Database\\MigrationRunner

        Sets the namespace the library should look for migration files:

        .. literalinclude:: migration/007.php

        .. note:: If you set ``null``, it looks for migration files in all namespaces.

    .. php:method:: setGroup($group)

        :param  string  $group: database group name.
        :returns:   The current MigrationRunner instance
        :rtype:     CodeIgniter\\Database\\MigrationRunner

        Sets the group the library should look for migration files:

        .. literalinclude:: migration/008.php
