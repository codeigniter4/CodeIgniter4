###################
Database Migrations
###################

Migrations are a convenient way for you to alter your database in a
structured and organized manner. You could edit fragments of SQL by hand
but you would then be responsible for telling other developers that they
need to go and run them. You would also have to keep track of which changes
need to be run against the production machines next time you deploy.

The database table **migration** tracks which migrations have already been
run so all you have to do is make sure your migrations are in place and
call ``$migration->latest()`` to bring the database up to the most recent
state. You can also use ``$migration->setNamespace(null)->progess()`` to
include migrations from all namespaces.

.. contents::
  :local:

.. raw:: html

  <div class="custom-index container"></div>

********************
Migration file names
********************

Each Migration is run in numeric order forward or backwards depending on the
method taken. Each migration is numbered using the timestamp when the migration
was created, in **YYYYMMDDHHIISS** format (e.g. **20121031100537**). This
helps prevent numbering conflicts when working in a team environment.

Prefix your migration files with the migration number followed by an underscore
and a descriptive name for the migration. The year, month, and date can be separated
from each other by dashes, underscores, or not at all. For example:

* 20121031100537_add_blog.php
* 2012-10-31-100538_alter_blog_track_views.php
* 2012_10_31_100539_alter_blog_add_translations.php


******************
Create a Migration
******************

This will be the first migration for a new site which has a blog. All
migrations go in the **app/Database/Migrations/** directory and have names such
as *20121031100537_add_blog.php*.
::

	<?php namespace App\Database\Migrations;

	class AddBlog extends \CodeIgniter\Database\Migration {

		public function up()
		{
			$this->forge->addField([
				'blog_id'          => [
					'type'           => 'INT',
					'constraint'     => 5,
					'unsigned'       => TRUE,
					'auto_increment' => TRUE
				],
				'blog_title'       => [
					'type'           => 'VARCHAR',
					'constraint'     => '100',
				],
				'blog_description' => [
					'type'           => 'TEXT',
					'null'           => TRUE,
				],
			]);
			$this->forge->addKey('blog_id', TRUE);
			$this->forge->createTable('blog');
		}

		public function down()
		{
			$this->forge->dropTable('blog');
		}
	}

The database connection and the database Forge class are both available to you through
``$this->db`` and ``$this->forge``, respectively.

Alternatively, you can use a command-line call to generate a skeleton migration file. See
below for more details.

Foreign Keys
============

When your tables include Foreign Keys, migrations can often cause problems as you attempt to drop tables and columns.
To temporarily bypass the foreign key checks while running migrations, use the ``disableForeignKeyChecks()`` and
``enableForeignKeyChecks()`` methods on the database connection.

::

    public function up()
    {
        $this->db->disableForeignKeyChecks();

        // Migration rules would go here...

        $this->db->enableForeignKeyChecks();
    }

Database Groups
===============

A migration will only be run against a single database group. If you have multiple groups defined in
**app/Config/Database.php**, then it will run against the ``$defaultGroup`` as specified
in that same configuration file. There may be times when you need different schemas for different
database groups. Perhaps you have one database that is used for all general site information, while
another database is used for mission critical data. You can ensure that migrations are run only
against the proper group by setting the ``$DBGroup`` property on your migration. This name must
match the name of the database group exactly::

    <?php namespace App\Database\Migrations;

    class AddBlog extends \CodeIgniter\Database\Migration
    {
        protected $DBGroup = 'alternate_db_group';

        public function up() { . . . }

        public function down() { . . . }
    }

Namespaces
==========

The migration library can automatically scan all namespaces you have defined within
**app/Config/Autoload.php** or loaded from an external source like Composer, using
the ``$psr4`` property for matching directory names. It will include all migrations
it finds in Database/Migrations.

Each namespace has it's own version sequence, this will help you upgrade and downgrade each module (namespace) without affecting other namespaces.

For example, assume that we have the following namespaces defined in our Autoload
configuration file::

	$psr4 = [
		'App'       => APPPATH,
		'MyCompany' => ROOTPATH.'MyCompany'
	];

This will look for any migrations located at both **APPPATH/Database/Migrations** and
**ROOTPATH/MyCompany/Database/Migrations**. This makes it simple to include migrations in your
re-usable, modular code suites.

*************
Usage Example
*************

In this example some simple code is placed in **app/Controllers/Migrate.php**
to update the schema::

    <?php namespace App\Controllers;

	class Migrate extends \CodeIgniter\Controller
	{

		public function index()
		{
			$migrate = \Config\Services::migrations();

			try
			{
			  $migrate->latest();
			}
			catch (\Exception $e)
			{
			  // Do something with the error here...
			}
		}

	}

*******************
Command-Line Tools
*******************
CodeIgniter ships with several :doc:`commands </cli/cli_commands>` that are available from the command line to help
you work with migrations. These tools are not required to use migrations but might make things easier for those of you
that wish to use them. The tools primarily provide access to the same methods that are available within the MigrationRunner class.

**migrate**

Migrates a database group with all available migrations::

    > php spark migrate

You can use (migrate) with the following options:

- (-g) to chose database group, otherwise default database group will be used.
- (-n) to choose namespace, otherwise (App) namespace will be used.
- (-all) to migrate all namespaces to the latest migration

This example will migrate Blog namespace with any new migrations on the test database group::

    > php spark migrate -g test -n Blog

When using the `-all` option, it will scan through all namespaces attempting to find any migrations that have
not been run. These will all be collected and then sorted as a group by date created. This should help
to minimize any potential conflicts between the main application and any modules.

**rollback**

Rolls back all migrations, taking the database group to a blank slate, effectively migration 0::

  > php spark migrate:rollback

You can use (rollback) with the following options:

- (-g) to choose database group, otherwise default database group will be used.
- (-b) to choose a batch: natural numbers specify the batch, negatives indicate a relative batch

**refresh**

Refreshes the database state by first rolling back all migrations, and then migrating all::

  > php spark migrate:refresh

You can use (refresh) with the following options:

- (-g) to choose database group, otherwise default database group will be used.
- (-n) to choose namespace, otherwise (App) namespace will be used.
- (-all) to refresh all namespaces

**status**

Displays a list of all migrations and the date and time they ran, or '--' if they have not been run::

  > php spark migrate:status
  Filename               Migrated On
  First_migration.php    2016-04-25 04:44:22

You can use (status) with the following options:

- (-g) to choose database group, otherwise default database group will be used.

**create**

Creates a skeleton migration file in **app/Database/Migrations**.
It automatically prepends the current timestamp. The class name it
creates is the Pascal case version of the filename.

  > php spark migrate:create [filename]


You can use (create) with the following options:

- (-n) to choose namespace, otherwise (App) namespace will be used.

*********************
Migration Preferences
*********************

The following is a table of all the config options for migrations, available in **app/Config/Migrations.php**.

========================== ====================== ========================== =============================================================
Preference                 Default                Options                    Description
========================== ====================== ========================== =============================================================
**enabled**                TRUE                   TRUE / FALSE               Enable or disable migrations.
**table**                  migrations             None                       The table name for storing the schema version number.
**timestampFormat**        Y-m-d-His\_                                        The format to use for timestamps when creating a migration.
========================== ====================== ========================== =============================================================

***************
Class Reference
***************

.. php:class:: CodeIgniter\\Database\\MigrationRunner

	.. php:method:: findMigrations()

		:returns:	An array of migration files
		:rtype:	array

		An array of migration filenames are returned that are found in the **path** property.

	.. php:method:: latest($group)

		:param	mixed	$group: database group name, if null default database group will be used.
		:returns:	TRUE on success, FALSE on failure
		:rtype:	bool

		This locates migrations for a namespace (or all namespaces), determines which migrations
		have not yet been run, and runs them in order of their version (namespaces intermingled).

	.. php:method:: regress($batch, $group)

		:param	mixed	$batch: previous batch to migrate down to; 1+ specifies the batch, 0 reverts all, negative refers to the relative batch (e.g. -3 means "three batches back")
		:param	mixed	$group: database group name, if null default database group will be used.
		:returns:	TRUE on success, FALSE on failure or no migrations are found
		:rtype:	bool

		Regress can be used to roll back changes to a previous state, batch by batch.
		::

			$migration->batch(5);
			$migration->batch(-1);

	.. php:method:: force($path, $namespace, $group)

		:param	mixed	$path:  path to a valid migration file.
		:param	mixed	$namespace: namespace of the provided migration.
		:param	mixed	$group: database group name, if null default database group will be used.
		:returns:	TRUE on success, FALSE on failure
		:rtype:	bool

		This forces a single file to migrate regardless of order or batches. Method "up" or "down" is detected based on whether it has already been migrated. **Note**: This method is recommended only for testing and could cause data consistency issues.

	.. php:method:: setNamespace($namespace)

	  :param  string  $namespace: application namespace.
	  :returns:   The current MigrationRunner instance
	  :rtype:     CodeIgniter\Database\MigrationRunner

	  Sets the path the library should look for migration files::

	    $migration->setNamespace($path)
	              ->latest();
	.. php:method:: setGroup($group)

	  :param  string  $group: database group name.
	  :returns:   The current MigrationRunner instance
	  :rtype:     CodeIgniter\Database\MigrationRunner

	  Sets the path the library should look for migration files::

	    $migration->setNamespace($path)
	              ->latest();
