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

}
