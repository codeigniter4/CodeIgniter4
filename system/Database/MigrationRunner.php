<?php namespace CodeIgniter\Database;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package      CodeIgniter
 * @author       CodeIgniter Dev Team
 * @copyright    2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license      https://opensource.org/licenses/MIT	MIT License
 * @link         https://codeigniter.com
 * @since        Version 3.0.0
 * @filesource
 */
use Config\Autoload;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Exceptions\ConfigException;

/**
 * Class MigrationRunner
 */
class MigrationRunner
{

	/**
	 * Whether or not migrations are allowed to run.
	 *
	 * @var bool
	 */
	protected $enabled = false;

	/**
	 * The type of migrations to use (sequential or timestamp)
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Name of table to store meta information
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * The version that current() will take us to.
	 *
	 * @var int
	 */
	protected $currentVersion = 0;

	/**
	 * The Namespace  where migrations can be found.
	 *
	 * @var string
	 */
	protected $namespace;

	/**
	 * The database Group to migrate.
	 *
	 * @var string
	 */
	protected $group;

	/**
	 * The migration name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The pattern used to locate migration file versions.
	 *
	 * @var string
	 */
	protected $regex;

	/**
	 * The main database connection. Used to store
	 * migration information in.
	 *
	 * @var ConnectionInterface
	 */
	protected $db;

	/**
	 * If true, will continue instead of throwing
	 * exceptions.
	 *
	 * @var bool
	 */
	protected $silent = false;

	/**
	 * used to return messages for CLI.
	 *
	 * @var bool
	 */
	protected $cliMessages = [];

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param BaseConfig                                $config
	 * @param \CodeIgniter\Database\ConnectionInterface $db
	 *
	 * @throws ConfigException
	 */
	public function __construct(BaseConfig $config, ConnectionInterface $db = null)
	{
		$this->enabled = $config->enabled ?? false;
		$this->type = $config->type ?? 'timestamp';
		$this->table = $config->table ?? 'migrations';
		$this->currentVersion = $config->currentVersion ?? 0;

		// Default name space is the app namespace
		$this->namespace = APP_NAMESPACE;

		// get default database group
		$config = new \Config\Database();
		$this->group = $config->defaultGroup;
		unset($config);

		if (empty($this->table))
		{
			throw ConfigException::forMissingMigrationsTable();
		}

		if ( ! in_array($this->type, ['sequential', 'timestamp']))
		{
			throw ConfigException::forInvalidMigrationType($this->type);
		}

		// Migration basename regex
		$this->regex = ($this->type === 'timestamp') ? '/^\d{14}_(\w+)$/' : '/^\d{3}_(\w+)$/';

		// If no db connection passed in, use
		// default database group.
		$this->db = ! empty($db) ? $db : \Config\Database::connect();

		$this->ensureTable();
	}

	//--------------------------------------------------------------------

	/**
	 * Migrate to a schema version
	 *
	 * Calls each migration step required to get to the schema version of
	 * choice
	 *
	 * @param    string   $targetVersion Target schema version
	 * @param    string   $namespace
	 * @param      string $group
	 *
	 * @return mixed TRUE if no migrations are found, current version string on success, FALSE on failure
	 * @throws ConfigException
	 */
	public function version(string $targetVersion, $namespace = null, $group = null)
	{
		if ( ! $this->enabled)
		{
			throw ConfigException::forDisabledMigrations();
		}
		// Set Namespace if not null
		if ( ! is_null($namespace))
		{
			$this->setNamespace($namespace);
		}

		// Set database group if not null
		if ( ! is_null($group))
		{
			$this->setGroup($group);
		}

		$migrations = $this->findMigrations();

		if (empty($migrations))
		{
			return true;
		}

		// Get Namespace current version
		// Note: We use strings, so that timestamp versions work on 32-bit systems
		$currentVersion = $this->getVersion();
		if ($targetVersion > $currentVersion)
		{
			// Moving Up
			$method = 'up';
			ksort($migrations);
		}
		else
		{
			// Moving Down, apply in reverse order
			$method = 'down';
			krsort($migrations);
		}

		// Check Migration consistency
		$this->CheckMigrations($migrations, $method, $targetVersion);

		// loop migration for each namespace (module)
		foreach ($migrations as $version => $migration)
		{

			// Only include migrations within the scoop
			if (($method === 'up' && $version > $currentVersion && $version <= $targetVersion) || ( $method === 'down' && $version <= $currentVersion && $version > $targetVersion)
			)
			{

				include_once $migration->path;
				// Get namespaced class name
				$class = $this->namespace . '\Database\Migrations\Migration_' . ($migration->name);

				$this->setName($migration->name);

				// Validate the migration file structure
				if ( ! class_exists($class, false))
				{
					throw new \RuntimeException(sprintf(lang('Migrations.classNotFound'), $class));
				}

				// Forcing migration to selected database group
				$instance = new $class(\Config\Database::forge($this->group));

				if ( ! is_callable([$instance, $method]))
				{
					throw new \RuntimeException(sprintf(lang('Migrations.missingMethod'), $method));
				}

				$instance->{$method}();
				if ($method === 'up')
				{
					$this->addHistory($migration->version);
				}
				elseif ($method === 'down')
				{
					$this->removeHistory($migration->version);
				}
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the schema to the latest migration
	 *
	 * @param string $namespace
	 * @param string $group
	 *
	 * @return    mixed    Current version string on success, FALSE on failure
	 */
	public function latest($namespace = null, $group = null)
	{

		// Set Namespace if not null
		if ( ! is_null($namespace))
		{
			$this->setNamespace($namespace);
		}
		// Set database group if not null
		if ( ! is_null($group))
		{
			$this->setGroup($group);
		}

		$migrations = $this->findMigrations();

		$lastMigration = end($migrations)->version ?? 0;

		// Calculate the last migration step from existing migration
		// filenames and proceed to the standard version migration
		return $this->version($lastMigration);
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the schema to the latest migration for all namespaces
	 *
	 * @param string $group
	 *
	 * @return bool
	 */
	public function latestAll($group = null)
	{
		// Set database group if not null
		if ( ! is_null($group))
		{
			$this->setGroup($group);
		}

		// Get all namespaces form  PSR4 paths.
		$config = new Autoload();
		$namespaces = $config->psr4;

		foreach ($namespaces as $namespace => $path)
		{

			$this->setNamespace($namespace);
			$migrations = $this->findMigrations();

			if (empty($migrations))
			{
				continue;
			}

			$lastMigration = end($migrations)->version;
			// No New migrations to add
			if ($lastMigration == $this->getVersion())
			{
				continue;
			}

			// Calculate the last migration step from existing migration
			// filenames and proceed to the standard version migration
			$this->version($lastMigration);
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the (APP_NAMESPACE) schema to $currentVersion in migration config file
	 *
	 * @param string $group
	 *
	 * @return    mixed    TRUE if no migrations are found, current version string on success, FALSE on failure
	 */
	public function current($group = null)
	{
		// Set database group if not null
		if ( ! is_null($group))
		{
			$this->setGroup($group);
		}

		return $this->version($this->currentVersion);
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves list of available migration scripts
	 *
	 * @return    array    list of migrations as $version for one namespace
	 */
	public function findMigrations()
	{
		$migrations = [];
		// Get namespace location form  PSR4 paths.
		$config = new Autoload();

		$location = $config->psr4[$this->namespace];

		// Setting migration directories.
		$dir = rtrim($location, DIRECTORY_SEPARATOR) . '/Database/Migrations/';

		// Load all *_*.php files in the migrations path
		foreach (glob($dir . '*_*.php') as $file)
		{
			$name = basename($file, '.php');
			// Filter out non-migration files
			if (preg_match($this->regex, $name))
			{
				// Create migration object using stdClass
				$migration = new \stdClass();
				// Get migration version number
				$migration->version = $this->getMigrationNumber($name);
				$migration->name = $this->getMigrationName($name);
				$migration->path = $file;

				// Add to migrations[version]
				$migrations[$migration->version] = $migration;
			}
		}

		return $migrations;
	}

	//--------------------------------------------------------------------

	/**
	 *  checks if the list of available migration scripts list are consistent
	 *  if sequential check if no gaps and check if all consistent with migrations table if downgrading
	 *  if timestamp check if consistent with migrations table if downgrading
	 *
	 * @param array  $migrations
	 * @param string $method
	 * @param int    $targetversion
	 *
	 * @return    bool
	 */
	protected function CheckMigrations($migrations, $method, $targetversion)
	{
		// Check if no migrations found
		if (empty($migrations))
		{
			if ($this->silent)
			{
				return false;
			}
			throw new \RuntimeException(lang('Migrations.empty'));
		}

		// Check if $targetversion file is found
		if ($targetversion != 0 && ! array_key_exists($targetversion, $migrations))
		{
			if ($this->silent)
			{
				return false;
			}
			throw new \RuntimeException(lang('Migrations.notFound') . $targetversion);
		}

		ksort($migrations);

		if ($method === 'down')
		{
			$history_migrations = $this->getHistory($this->group);
			$history_size = count($history_migrations) - 1;
		}
		// Check for sequence gaps
		$loop = 0;
		foreach ($migrations as $migration)
		{
			if ($this->type === 'sequential' && abs($migration->version - $loop) > 1)
			{
				throw new \RuntimeException(lang('Migration.gap') . " " . $migration->version);
			}
			// Check if all old migration files are all available to do downgrading
			if ($method === 'down')
			{
				if ($loop <= $history_size && $history_migrations[$loop]['version'] != $migration->version)
				{
					throw new \RuntimeException(lang('Migration.gap') . " " . $migration->version);
				}
			}
			$loop ++;
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Set namespace.
	 * Allows other scripts to modify on the fly as needed.
	 *
	 * @param string $namespace
	 *
	 * @return MigrationRunner
	 */
	public function setNamespace(string $namespace)
	{
		$this->namespace = $namespace;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Set database Group.
	 * Allows other scripts to modify on the fly as needed.
	 *
	 * @param string $group
	 *
	 * @return MigrationRunner
	 */
	public function setGroup(string $group)
	{
		$this->group = $group;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Set migration Name.
	 *
	 * @param string $name
	 */
	public function setName(string $name)
	{
		$this->name = $name;
	}

	//--------------------------------------------------------------------

	/**
	 * Grabs the full migration history from the database.
	 *
	 * @param string $group
	 *
	 * @return array
	 */
	public function getHistory($group = 'default')
	{
		$query = $this->db->table($this->table)
				->where('group', $group)
				->where('namespace', $this->namespace)
				->orderBy('version', 'ASC')
				->get();

		if ( ! $query)
		{
			return [];
		}

		return $query->getResultArray();
	}

	//--------------------------------------------------------------------

	/**
	 * If $silent == true, then will not throw exceptions and will
	 * attempt to continue gracefully.
	 *
	 * @param bool $silent
	 *
	 * @return MigrationRunner
	 */
	public function setSilent(bool $silent)
	{
		$this->silent = $silent;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Extracts the migration number from a filename
	 *
	 * @param    string $migration
	 *
	 * @return    string    Numeric portion of a migration filename
	 */
	protected function getMigrationNumber($migration)
	{
		return sscanf($migration, '%[0-9]+', $number) ? $number : '0';
	}

	//--------------------------------------------------------------------

	/**
	 * Extracts the migration class name from a filename
	 *
	 * @param    string $migration
	 *
	 * @return    string    text portion of a migration filename
	 */
	protected function getMigrationName($migration)
	{
		$parts = explode('_', $migration);
		array_shift($parts);

		return implode('_', $parts);
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves current schema version
	 *
	 * @return    string    Current migration version
	 */
	protected function getVersion()
	{
		$row = $this->db->table($this->table)
				->select('version')
				->where('group', $this->group)
				->where('namespace', $this->namespace)
				->orderBy('version', 'DESC')
				->get();

		return $row && ! is_null($row->getRow()) ? $row->getRow()->version : '0';
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves current schema version
	 *
	 * @return    string    Current migration version
	 */
	public function getCliMessages()
	{

		return $this->cliMessages;
	}

	//--------------------------------------------------------------------

	/**
	 * Stores the current schema version.
	 *
	 * @param string $version
	 *
	 * @internal param string $migration Migration reached
	 *
	 */
	protected function addHistory($version)
	{
		$this->db->table($this->table)
				->insert([
					'version'	 => $version,
					'name'		 => $this->name,
					'group'		 => $this->group,
					'namespace'	 => $this->namespace,
					'time'		 => time(),
		]);
		if (is_cli())
		{
			$this->cliMessages[] = "\t" . CLI::color(lang('Migrations.added'), 'yellow') . "($this->namespace) " . $version . '_' . $this->name;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a single history
	 *
	 * @param string $version
	 */
	protected function removeHistory($version)
	{
		$this->db->table($this->table)
				->where('version', $version)
				->where('group', $this->group)
				->where('namespace', $this->namespace)
				->delete();
		if (is_cli())
		{
			$this->cliMessages[] = "\t" . CLI::color(lang('Migrations.removed'), 'yellow') . "($this->namespace) " . $version . '_' . $this->name;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures that we have created our migrations table
	 * in the database.
	 */
	protected function ensureTable()
	{
		if ($this->db->tableExists($this->table))
		{
			return;
		}

		$forge = \Config\Database::forge();

		$forge->addField([
			'version'	 => [
				'type'		 => 'VARCHAR',
				'constraint' => 255,
				'null'		 => false,
			],
			'name'		 => [
				'type'		 => 'VARCHAR',
				'constraint' => 255,
				'null'		 => false,
			],
			'group'		 => [
				'type'		 => 'VARCHAR',
				'constraint' => 255,
				'null'		 => false,
			],
			'namespace'	 => [
				'type'		 => 'VARCHAR',
				'constraint' => 255,
				'null'		 => false,
			],
			'time'		 => [
				'type'		 => 'INT',
				'constraint' => 11,
				'null'		 => false,
			],
		]);

		$forge->createTable($this->table, true);
	}

	//--------------------------------------------------------------------
}
