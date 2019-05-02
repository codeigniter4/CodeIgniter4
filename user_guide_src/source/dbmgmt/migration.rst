###################
Database Migrations
###################

Migrations are a convenient way for you to alter your database in a
structured and organized manner. You could edit fragments of SQL by hand
but you would then be responsible for telling other developers that they
need to go and run them. You would also have to keep track of which changes
need to be run against the production machines next time you deploy.

The database table **migration** tracks which migrations have already been
run so all you have to do is update your application files and
call ``$migration->current()`` to work out which migrations should be run.
The current version is found in **app/Config/Migrations.php**.

.. contents::
  :local:

.. raw:: html

  <div class="custom-index container"></div>

********************
Migration file names
********************

Each Migration is run in numeric order forward or backwards depending on the
method taken. Two numbering styles are available:

* **Sequential:** each migration is numbered in sequence, starting with **001**.
  Each number must be three digits, and there must not be any gaps in the
  sequence. (This was the numbering scheme prior to CodeIgniter 3.0.)
* **Timestamp:** each migration is numbered using the timestamp when the migration
  was created, in **YYYYMMDDHHIISS** format (e.g. **20121031100537**). This
  helps prevent numbering conflicts when working in a team environment, and is
  the preferred scheme in CodeIgniter 3.0 and later.

The desired style may be selected using the ``$type`` setting in your
*app/Config/Migrations.php* file. The default setting is timestamp.

Regardless of which numbering style you choose to use, prefix your migration
files with the migration number followed by an underscore and a descriptive
name for the migration. For example:

* 001_add_blog.php (sequential numbering)
* 20121031100537_add_blog.php (timestamp numbering)

******************
Create a Migration
******************

This will be the first migration for a new site which has a blog. All
migrations go in the **app/Database/Migrations/** directory and have names such
as *20121031100537_Add_blog.php*.
::

	<?php namespace App\Database\Migrations;

	class Migration_Add_blog extends \CodeIgniter\Database\Migration {

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

Then in **app/Config/Migrations.php** set ``$currentVersion = 20121031100537;``.

The database connection and the database Forge class are both available to you through
``$this->db`` and ``$this->forge``, respectively.

Alternatively, you can use a command-line call to generate a skeleton migration file. See
below for more details.

Using $currentVersion
=====================

The $currentVersion setting allows you to mark a location that your main application namespace should be set at.
This is especially helpful for use in a production setting. In your application, you can always
update the migration to the current version, and not latest to ensure your production and staging
servers are running the correct schema. On your development servers, you can add additional migrations
for code that is not ready for production, yet. By using the ``latest()`` method, you can be assured
that your development machines are always running the bleeding edge schema.

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

    class Migration_Add_blog extends \CodeIgniter\Database\Migration
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
**ROOTPATH/Database/Migrations**. This makes it simple to include migrations in your
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
			  $migrate->current();
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

**latest**

Migrates all database groups to the latest available migrations::

    > php spark migrate:latest

You can use (latest) with the following options:

- (-g) to chose database group, otherwise default database group will be used.
- (-n) to choose namespace, otherwise (App) namespace will be used.
- (-all) to migrate all namespaces to the latest migration

This example will migrate Blog namespace to latest::

    > php spark migrate:latest -g test -n Blog

**current**

Migrates the (App) namespace to match the version set in ``$currentVersion``. This will migrate both
up and down as needed to match the specified version::

    > php spark migrate:current

You can use (current) with the following options:

- (-g) to chose database group, otherwise default database group will be used.

**version**

Migrates to the specified version. If no version is provided, you will be prompted
for the version. ::

  // Asks you for the version...
  > php spark migrate:version
  Version:

  // Sequential
  > php spark migrate:version 007

  // Timestamp
  > php spark migrate:version 20161426211300

You can use (version) with the following options:

- (-g) to chose database group, otherwise default database group will be used.
- (-n) to choose namespace, otherwise (App) namespace will be used.

**rollback**

Rolls back all migrations, taking all database groups to a blank slate, effectively migration 0::

  > php spark migrate:rollback

You can use (rollback) with the following options:

- (-g) to chose database group, otherwise default database group will be used.
- (-n) to choose namespace, otherwise (App) namespace will be used.
- (-all) to migrate all namespaces to the latest migration

**refresh**

Refreshes the database state by first rolling back all migrations, and then migrating to the latest version::

  > php spark migrate:refresh

You can use (refresh) with the following options:

- (-g) to chose database group, otherwise default database group will be used.
- (-n) to choose namespace, otherwise (App) namespace will be used.
- (-all) to migrate all namespaces to the latest migration

**status**

Displays a list of all migrations and the date and time they ran, or '--' if they have not been run::

  > php spark migrate:status
  Filename               Migrated On
  First_migration.php    2016-04-25 04:44:22

You can use (refresh) with the following options:

- (-g) to chose database group, otherwise default database group will be used.

**create**

Creates a skeleton migration file in **app/Database/Migrations**.

- When migration type is timestamp, using the YYYYMMDDHHIISS format::

  > php spark migrate:create [filename]

- When migration type is sequential, using the numbered in sequence, default with 001::

  > php spark migrate:create [filename] 001

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
**path**                   'Database/Migrations/' None                       The path to your migrations folder.
**currentVersion**         0                      None                       The current version your database should use.
**table**                  migrations             None                       The table name for storing the schema version number.
**type**                   'timestamp'            'timestamp' / 'sequential' The type of numeric identifier used to name migration files.
========================== ====================== ========================== =============================================================

***************
Class Reference
***************

.. php:class:: CodeIgniter\Database\MigrationRunner

	.. php:method:: current($group)

		:param	mixed	$group: database group name, if null (App) namespace will be used.
		:returns:	TRUE if no migrations are found, current version string on success, FALSE on failure
		:rtype:	mixed

		Migrates up to the current version (whatever is set for
		``$currentVersion`` in *app/Config/Migrations.php*).

	.. php:method:: findMigrations()

		:returns:	An array of migration files
		:rtype:	array

		An array of migration filenames are returned that are found in the **path** property.

	.. php:method:: latest($namespace, $group)

		:param	mixed	$namespace: application namespace, if null (App) namespace will be used.
		:param	mixed	$group: database group name, if null default database group will be used.
		:returns:	Current version string on success, FALSE on failure
		:rtype:	mixed

		This works much the same way as ``current()`` but instead of looking for
		the ``$currentVersion`` the Migration class will use the very
		newest migration found in the filesystem.
	.. php:method:: latestAll($group)

		:param	mixed	$group: database group name, if null default database group will be used.
		:returns:	TRUE on success, FALSE on failure
		:rtype:	mixed

		This works much the same way as ``latest()`` but instead of looking for
		one namespace, the Migration class will use the very
		newest migration found for all namespaces.
	.. php:method:: version($target_version, $namespace, $group)

		:param	mixed	$namespace: application namespace, if null (App) namespace will be used.
		:param	mixed	$group: database group name, if null default database group will be used.
		:param	mixed	$target_version: Migration version to process
		:returns:	Current version string on success, FALSE on failure or no migrations are found
		:rtype:	mixed

		Version can be used to roll back changes or step forwards programmatically to
		specific versions. It works just like ``current()`` but ignores ``$currentVersion``.
		::

			$migration->version(5);

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
