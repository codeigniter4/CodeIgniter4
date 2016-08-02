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
The current version is found in **application/Config/Migrations.php**.

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
*application/Config/Migrations.php* file. The default setting is timestamp.

Regardless of which numbering style you choose to use, prefix your migration
files with the migration number followed by an underscore and a descriptive
name for the migration. For example:

* 001_add_blog.php (sequential numbering)
* 20121031100537_add_blog.php (timestamp numbering)

******************
Create a Migration
******************
	
This will be the first migration for a new site which has a blog. All 
migrations go in the **application/Database/Migrations/** directory and have names such
as *20121031100537_Add_blog.php*.
::

	<?php

	class Migration_Add_blog extends \CodeIgniter\Database\Migration {

		public function up()
		{
			$this->forge->addField(array(
				'blog_id' => array(
					'type' => 'INT',
					'constraint' => 5,
					'unsigned' => TRUE,
					'auto_increment' => TRUE
				),
				'blog_title' => array(
					'type' => 'VARCHAR',
					'constraint' => '100',
				),
				'blog_description' => array(
					'type' => 'TEXT',
					'null' => TRUE,
				),
			));
			$this->forge->addKey('blog_id', TRUE);
			$this->forge->createTable('blog');
		}

		public function down()
		{
			$this->forge->dropTable('blog');
		}
	}

Then in **application/Config/Migrations.php** set ``$currentVersion = 20121031100537;``.

The database connection and the database Forge class are both available to you through
``$this->db`` and ``$this->forge``, respectively.

Using $currentVersion
=====================

The $currentVersion setting allows you to mark a location that your application should be set at.
This is especially helpful for use in a production setting. In your application, you can always
update the migration to the current version, and not latest to ensure your production and staging
servers are running the correct schema. On your development servers, you can add additional migrations
for code that is not ready for production, yet. By using the ``latest()`` method, you can be assured
that your development machines are always running the bleeding edge schema.

Database Groups
===============

A migration will only be ran against a single database group. If you have multiple groups defined in
**application/Config/Database.php**, then it will run against the ``$defaultGroup`` as specified
in that same configuration file. There may be times when you need different schemas for different
database groups. Perhaps you have one database that is used for all general site information, while
another database is used for mission critical data. You can ensure that migrations are run only
against the proper group by setting the ``$DBGroup`` property on your migration. This name must
match the name of the database group exactly::

  class Migration_Add_blog extends \CodeIgniter\Database\Migration
  {
    protected $DBGroup = 'alternate_db_group';

    public function up() { . . . }

    public function down() { . . . }
  }

*************
Usage Example
*************

In this example some simple code is placed in **application/controllers/Migrate.php** 
to update the schema::

	<?php
	
	class Migrate extends CI_Controller
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
Commnand-Line Tools
*******************

CodeIgniter ships with some tools that are available from the command line to help you work with migrations.
These tools are not required to use migrations but might make things easier for those of you that wish to use them.
The tools primarily provide access to the same methods that are available within the MigrationRunner class.
When running these commands, you should be in the same directory as your application's main index.php file.

**latest**

Migrates all database groups to the latest available migrations::

  > php index.php migrations latest

**current**

Migrates all database groups to match the version set in ``$currentVersion``. This will migrate both
up and down as needed to match the specified version::

  > php index.php migrations current

**version**

Migrates all database groups to the specified version. If no version is provided, you will be prompted
for the version. ::

  // Asks you for the version...
  > php index.php migrations version
  > Version:

  // Sequential
  > php index.php migrations version 007

  // Timestamp
  > php index.php migrations version 20161426211300

**rollback**

Rolls back all migrations, taking all database groups to a blank slate, effectively migration 0::

  > php index.php migrations rollback

**refresh**

Refreshes the database state by first rolling back all migrations, and then migrating to the latest version::

  > php index.php migrations refresh

**status**

Displays a list of all migrations and the date and time they were ran, or '--' if they have not be ran::

  > php index.php migrations status
  Filename                              Migrated On
  20150101101500_First_migration.php    2016-04-25 04:44:22


*********************
Migration Preferences
*********************

The following is a table of all the config options for migrations, available in **application/Config/Migrations.php**.

========================== ====================== ========================== =============================================================
Preference                 Default                Options                    Description
========================== ====================== ========================== =============================================================
**enabled**                FALSE                  TRUE / FALSE               Enable or disable migrations.
**path**                   APPPATH.'migrations/'  None                       The path to your migrations folder.
**currentVersion**         0                      None                       The current version your database should use.
**table**                  migrations             None                       The table name for storing the schema version number.
**type**                   'timestamp'            'timestamp' / 'sequential' The type of numeric identifier used to name migration files.
========================== ====================== ========================== =============================================================

***************
Class Reference
***************

.. php:class:: CodeIgniter\Database\MigrationRunner

	.. php:method:: current()

		:returns:	TRUE if no migrations are found, current version string on success, FALSE on failure
		:rtype:	mixed

		Migrates up to the current version (whatever is set for
		``$currentVersion`` in *application/Config/Migrations.php*).

	.. php:method:: findMigrations()

		:returns:	An array of migration files
		:rtype:	array

		An array of migration filenames are returned that are found in the **path** property.

	.. php:method:: latest()

		:returns:	Current version string on success, FALSE on failure
		:rtype:	mixed

		This works much the same way as ``current()`` but instead of looking for 
		the ``$currentVersion`` the Migration class will use the very
		newest migration found in the filesystem.

	.. php:method:: version($target_version)

		:param	mixed	$target_version: Migration version to process
		:returns:	TRUE if no migrations are found, current version string on success, FALSE on failure
		:rtype:	mixed

		Version can be used to roll back changes or step forwards programmatically to 
		specific versions. It works just like ``current()`` but ignores ``$currentVersion``.
		::

			$migration->version(5);

	.. php:method:: setPath($path)

	  :param  string  $path: The directory where migration files can be found.
	  :returns:   The current MigrationRunner instance
	  :rtype:     CodeIgniter\Database\MigrationRunner

	  Sets the path the library should look for migration files::

	    $migration->setPath($path)
	              ->latest();

