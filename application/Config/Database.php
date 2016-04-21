<?php namespace Config;

/**
 * Database Configuration
 *
 * @package Config
 */
class Database extends \CodeIgniter\Database\Config
{
	/**
	 * Lets you choose which connection group to
	 * use if no other is specified.
	 *
	 * @var string
	 */
	public $defaultGroup = 'default';

	/**
	 * The default database connection.
	 *
	 * @var array
	 */
	public $default = [
		'DSN'          => '',
		'hostname'     => 'localhost',
		'username'     => '',
		'password'     => '',
		'database'     => '',
		'DBDriver'     => 'MySQLi',
		'DBPrefix'     => '',
		'pConnect'     => false,
		'DBDebug'     => (ENVIRONMENT !== 'production'),
		'cacheOn'     => false,
		'cacheDir'     => '',
		'charset'      => 'utf8',
		'DBCollat'     => 'utf8_general_ci',
		'swapPre'      => '',
		'encrypt'      => false,
		'compress'     => false,
		'strictOn'     => false,
		'failover'     => [],
		'saveQueries' => true,
	];

	/**
	 * This database connection is used when
	 * running PHPUnit database tests.
	 *
	 * @var array
	 */
	public $tests = [
		'DSN'          => '',
		'hostname'     => 'localhost',
		'username'     => '',
		'password'     => '',
		'database'     => '',
		'DBDriver'     => 'MySQLi',
		'DBPrefix'     => '',
		'pConnect'     => false,
		'DBDebug'     => (ENVIRONMENT !== 'production'),
		'cacheOn'     => false,
		'cacheDir'     => '',
		'charset'      => 'utf8',
		'DBCollat'     => 'utf8_general_ci',
		'swapPre'      => '',
		'encrypt'      => false,
		'compress'     => false,
		'strictOn'     => false,
		'failover'     => [],
		'saveQueries' => true,
	];

	//--------------------------------------------------------------------

}
