<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Migrations extends BaseConfig
{
	/*
	|--------------------------------------------------------------------------
	| Enable/Disable Migrations
	|--------------------------------------------------------------------------
	|
	| Migrations are disabled by default for security reasons.
	| You should enable migrations whenever you intend to do a schema migration
	| and disable it back when you're done.
	|
	*/
	public $enabled = true;

	/*
	|--------------------------------------------------------------------------
	| Migration Type
	|--------------------------------------------------------------------------
	|
	| Migration file names may be based on a sequential identifier or on
	| a timestamp. Options are:
	|
	|   'sequential' = Sequential migration naming (001_add_blog.php)
	|   'timestamp'  = Timestamp migration naming (20121031104401_add_blog.php)
	|                  Use timestamp format YYYYMMDDHHIISS.
	|
	| Note: If this configuration value is missing the Migration library
	|       defaults to 'sequential' for backward compatibility with CI2.
	|
	*/
	public $type = 'timestamp';

	/*
	|--------------------------------------------------------------------------
	| Migrations table
	|--------------------------------------------------------------------------
	|
	| This is the name of the table that will store the current migrations state.
	| When migrations runs it will store in a database table which migration
	| level the system is at. It then compares the migration level in this
	| table to the $config['migration_version'] if they are not the same it
	| will migrate up. This must be set.
	|
	*/
	public $table = 'migrations';

	/*
	|--------------------------------------------------------------------------
	| Migrations version
	|--------------------------------------------------------------------------
	|
	| This is used to set migration version that the file system should be on.
	| If you run $this->migration->current() this is the version that schema will
	| be upgraded / downgraded to.
	|
	*/
	public $currentVersion = 0;

}
