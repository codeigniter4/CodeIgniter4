<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
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
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Database;

use Config\Services;
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
	 * @var boolean
	 */
	protected $enabled = false;

	/**
	 * Name of table to store meta information
	 *
	 * @var string
	 */
	protected $table;

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
	protected $regex = '/^\d{4}[_-]?\d{2}[_-]?\d{2}[_-]?\d{6}_(\w+)$/';

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
	 * @var boolean
	 */
	protected $silent = false;

	/**
	 * used to return messages for CLI.
	 *
	 * @var array
	 */
	protected $cliMessages = [];

	/**
	 * Tracks whether we have already ensured
	 * the table exists or not.
	 *
	 * @var boolean
	 */
	protected $tableChecked = false;

	/**
	 * The full path to locate migration files.
	 *
	 * @var string
	 */
	protected $path;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * When passing in $db, you may pass any of the following to connect:
	 * - group name
	 * - existing connection instance
	 * - array of database configuration values
	 *
	 * @param BaseConfig                                             $config
	 * @param \CodeIgniter\Database\ConnectionInterface|array|string $db
	 *
	 * @throws ConfigException
	 */
	public function __construct(BaseConfig $config, $db = null)
	{
		$this->enabled = $config->enabled ?? false;
		$this->table   = $config->table ?? 'migrations';

		// Default name space is the app namespace
		$this->namespace = APP_NAMESPACE;

		// get default database group
		$config      = config('Database');
		$this->group = $config->defaultGroup;
		unset($config);

		// If no db connection passed in, use
		// default database group.
		$this->db = db_connect($db);
	}

	//--------------------------------------------------------------------

	/**
	 * Migrate to a schema version
	 *
	 * Calls each migration step required to get to the schema version of
	 * choice
	 *
	 * @param string      $targetVersion Target schema version
	 * @param string|null $namespace
	 * @param string|null $group
	 *
	 * @return mixed Current version string on success, FALSE on failure or no migrations are found
	 * @throws ConfigException
	 */
	public function version(string $targetVersion, string $namespace = null, string $group = null)
	{
		if (! $this->enabled)
		{
			throw ConfigException::forDisabledMigrations();
		}

		$this->ensureTable();

		// Set Namespace if not null
		if (! is_null($namespace))
		{
			$this->setNamespace($namespace);
		}

		// Set database group if not null
		if (! is_null($group))
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

		list($method, $migrations) = $this->determineDirection($targetVersion, $currentVersion, $migrations);

		// Check Migration consistency
		$migrationStatus = $this->migrate($method, $migrations, $targetVersion);

		return ($migrationStatus) ? $targetVersion : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the schema to the latest migration
	 *
	 * @param string|null $namespace
	 * @param string|null $group
	 *
	 * @return mixed    Current version string on success, FALSE on failure
	 */
	public function latest(string $namespace = null, string $group = null)
	{
		$this->ensureTable();

		// Set Namespace if not null
		if (! is_null($namespace))
		{
			$this->setNamespace($namespace);
		}
		// Set database group if not null
		if (! is_null($group))
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
	 * @param string|null $group
	 *
	 * @return boolean
	 */
	public function latestAll(string $group = null): bool
	{
		$this->ensureTable();

		// Set database group if not null
		if (! is_null($group))
		{
			$this->setGroup($group);
		}

		// Get all namespaces from the autoloader
		$namespaces = Services::autoloader()->getNamespace();

		// Collect the migrations to run
		$migrations = [];

		foreach ($namespaces as $namespace => $paths)
		{
			$this->setNamespace($namespace);
			$nsMigrations = $this->findMigrations();

			if (empty($nsMigrations))
			{
				continue;
			}

			$migrations = array_merge($migrations, $nsMigrations);
		}

		$migrationStatus = $this->migrate('up', $migrations, end($migrations)->version);

		return $migrationStatus;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves list of available migration scripts for one namespace
	 *
	 * @return array    list of migrations as $version for one namespace
	 */
	public function findMigrations(): array
	{
		$migrations = [];
		$locator    = Services::locator(true);

		// If $this->path contains a valid directory use it.
		if (! empty($this->path))
		{
			helper('filesystem');
			$dir   = rtrim($this->path, DIRECTORY_SEPARATOR) . '/';
			$files = get_filenames($dir, true);
		}
		// Otherwise use FileLocator to search files in the subdirectory of the namespace
		else
		{
			$files = $locator->listNamespaceFiles($this->namespace, '/Database/Migrations/');
		}

		// Load all *_*.php files in the migrations path
		// We can't use glob if we want it to be testable....
		foreach ($files as $file)
		{
			if (substr($file, -4) !== '.php')
			{
				continue;
			}

			// Remove the extension
			$name = basename($file, '.php');

			// Filter out non-migration files
			if (preg_match($this->regex, $name))
			{
				// Create migration object using stdClass
				$migration = new \stdClass();

				// Get migration version number
				$migration->version = $this->getMigrationNumber($name);
				$migration->name    = $this->getMigrationName($name);
				$migration->path    = ! empty($this->path) && strpos($file, $this->path) !== 0
					? $this->path . $file
					: $file;
				$migration->class   = $locator->getClassname($file);

				// Add to migrations[version]
				$migrations[$migration->version] = $migration;
			}
		}

		ksort($migrations);

		return $migrations;
	}

	//--------------------------------------------------------------------

	/**
	 *  checks if the list of available migration scripts list are consistent
	 *  if timestamp check if consistent with migrations table if downgrading
	 *
	 * @param array  $migrations
	 * @param string $method
	 * @param string $targetVersion
	 *
	 * @return boolean
	 */
	protected function checkMigrations(array $migrations, string $method, string $targetVersion): bool
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

		// Check if $targetVersion file is found
		if ((int)$targetVersion !== 0 && ! array_key_exists($targetVersion, $migrations))
		{
			if ($this->silent)
			{
				return false;
			}
			throw new \RuntimeException(lang('Migrations.notFound') . $targetVersion);
		}

		ksort($migrations);

		if ($method === 'down')
		{
			$history_migrations = $this->getHistory($this->group);
			$history_size       = count($history_migrations) - 1;
		}
		// Check for sequence gaps
		$loop = 0;

		foreach ($migrations as $migration)
		{
			// Check if all old migration files are all available to do downgrading
			if ($method === 'down')
			{
				if ($loop <= $history_size && $history_migrations[$loop]['version'] !== $migration->version)
				{
					throw new \RuntimeException(lang('Migrations.gap') . ' ' . $migration->version);
				}
			}
			$loop++;
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the path to the base directory that will be used
	 * when locating migrations. If left null, the value will
	 * be chosen from $this->namespace's directory.
	 *
	 * @param string|null $path
	 *
	 * @return $this
	 */
	public function setPath(string $path = null)
	{
		$this->path = $path;

		return $this;
	}

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
	 *
	 * @return \CodeIgniter\Database\MigrationRunner
	 */
	public function setName(string $name)
	{
		$this->name = $name;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Grabs the full migration history from the database.
	 *
	 * @param string $group
	 *
	 * @return array
	 */
	public function getHistory(string $group = 'default'): array
	{
		$this->ensureTable();

		$query = $this->db->table($this->table)
						  ->where('group', $group)
						  ->where('namespace', $this->namespace)
						  ->orderBy('version', 'ASC')
						  ->get();

		if (! $query)
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
	 * @param boolean $silent
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
	 * @param string $migration
	 *
	 * @return string    Numeric portion of a migration filename
	 */
	protected function getMigrationNumber(string $migration): string
	{
		preg_match('/^\d{4}[_-]?\d{2}[_-]?\d{2}[_-]?\d{6}/', $migration, $matches);

		return count($matches) ? $matches[0] : '0';
	}

	//--------------------------------------------------------------------

	/**
	 * Extracts the migration class name from a filename
	 *
	 * @param string $migration
	 *
	 * @return string    text portion of a migration filename
	 */
	protected function getMigrationName(string $migration): string
	{
		$parts = explode('_', $migration);
		array_shift($parts);

		return implode('_', $parts);
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves current schema version
	 *
	 * @return string    Current migration version
	 */
	protected function getVersion(): string
	{
		$this->ensureTable();

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
	 * @return array    Current migration version
	 */
	public function getCliMessages(): array
	{
		return $this->cliMessages;
	}

	//--------------------------------------------------------------------

	/**
	 * Stores the current schema version.
	 *
	 * @param string  $version
	 * @param integer $batch
	 *
	 * @return   void
	 * @internal param string $migration Migration reached
	 */
	protected function addHistory(string $version, int $batch = null)
	{
		if (empty($batch))
		{
			$batch = $this->getLastBatch() + 1;
		}

		$this->db->table($this->table)
				 ->insert([
					 'version'   => $version,
					 'name'      => $this->name,
					 'group'     => $this->group,
					 'namespace' => $this->namespace,
					 'time'      => time(),
					 'batch'     => $batch,
				 ]);
		if (is_cli())
		{
			$this->cliMessages[] = "\t" . CLI::color(lang('Migrations.added'),
					'yellow') . "($this->namespace) " . $version . '_' . $this->name;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a single history
	 *
	 * @param string $version
	 *
	 * @return void
	 */
	protected function removeHistory(string $version)
	{
		$this->db->table($this->table)
				 ->where('version', $version)
				 ->where('group', $this->group)
				 ->where('namespace', $this->namespace)
				 ->delete();

		if (is_cli())
		{
			$this->cliMessages[] = "\t" . CLI::color(lang('Migrations.removed'),
					'yellow') . "($this->namespace) " . $version . '_' . $this->name;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Truncates the history table.
	 *
	 * @return boolean
	 */
	public function clearHistory()
	{
		if ($this->db->tableExists($this->table))
		{
			$this->db->table($this->table)
					 ->truncate();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the value of the last batch in the database.
	 *
	 * @return integer
	 */
	public function getLastBatch(): int
	{
		$this->ensureTable();

		$batch = $this->db->table($this->table)
						  ->selectMax('batch')
						  ->get()
						  ->getResultObject();

		$batch = is_array($batch) && count($batch)
			? end($batch)->batch
			: 0;

		return (int)$batch;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the version number of the first migration for a batch.
	 *
	 * @param integer $batch
	 *
	 * @return string
	 */
	public function getBatchStart(int $batch): string
	{
		$migration = $this->db->table($this->table)
			->where('batch', $batch)
			->orderBy('id', 'asc')
			->limit(1)
			->get()
			->getResultObject();

		return count($migration) ? $migration[0]->version : '0';
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the version number of the last migration for a batch.
	 *
	 * @param integer $batch
	 *
	 * @return string
	 */
	public function getBatchEnd(int $batch): string
	{
		$migration = $this->db->table($this->table)
			  ->where('batch', $batch)
			  ->orderBy('id', 'desc')
			  ->limit(1)
			  ->get()
			  ->getResultObject();

		return count($migration) ? $migration[0]->version : 0;
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures that we have created our migrations table
	 * in the database.
	 */
	public function ensureTable()
	{
		if ($this->tableChecked || $this->db->tableExists($this->table))
		{
			return;
		}

		$forge = \Config\Database::forge($this->db);

		$forge->addField([
			'id'        => [
				'type'           => 'INTEGER',
				'constraint'     => 255,
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'version'   => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
				'null'       => false,
			],
			'class'     => [
				'type' => 'TEXT',
				'null' => false,
			],
			'group'     => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
				'null'       => false,
			],
			'namespace' => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
				'null'       => false,
			],
			'time'      => [
				'type'       => 'INT',
				'constraint' => 11,
				'null'       => false,
			],
			'batch'     => [
				'type'       => 'INT',
				'constraint' => 11,
				'null'       => false,
			],
		]);

		$forge->addPrimaryKey('id');
		$forge->createTable($this->table, true);

		$this->tableChecked = true;
	}

	/**
	 * @param string $targetVersion
	 * @param string $currentVersion
	 * @param array  $migrations
	 *
	 * @return array
	 */
	protected function determineDirection(string $targetVersion, string $currentVersion, array &$migrations): array
	{
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

		return [
			$method,
			$migrations,
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Given an array of history items will either remove them
	 * of add them to the table.
	 *
	 * @param array   $histories
	 * @param string  $method
	 * @param integer $batch
	 */
	protected function updateHistory(array $histories, string $method, int $batch)
	{
		if ($method === 'up')
		{
			$time = time();

			foreach ($histories as $history)
			{
				$this->db->table($this->table)
						 ->insert([
							 'version'   => $history->version,
							 'class'     => $history->class,
							 'group'     => $this->group,
							 'namespace' => $this->namespace,
							 'time'      => $time,
							 'batch'     => $batch,
						 ]);
			}
		}
		elseif ($method === 'down')
		{
			$classes = [];

			foreach ($histories as $history)
			{
				$classes[] = $history->class;
			}

			if (count($classes))
			{
				$this->db->table($this->table)
						 ->whereIn('class', $classes)
						 ->delete();
			}
		}
	}

	/**
	 * Handles the actual running of migrations.
	 *
	 * @param $direction
	 * @param $migrations
	 * @param string     $targetVersion
	 *
	 * @return boolean
	 */
	protected function migrate($direction, $migrations, string $targetVersion): bool
	{
		$this->checkMigrations($migrations, $direction, $targetVersion);

		$batch          = $this->getLastBatch() + 1;
		$currentVersion = $this->getVersion();

		// loop migration for each namespace (module)
		$migrationStatus = false;
		$history         = [];
		foreach ($migrations as $version => $migration)
		{
			// Only include migrations within the scope
			if (($direction === 'up' && $version > $currentVersion && $version <= $targetVersion) || ($direction === 'down' && $version <= $currentVersion && $version >= $targetVersion))
			{
				$migrationStatus = false;
				include_once $migration->path;

				$class = $migration->class;
				$this->setName($migration->name);

				// Validate the migration file structure
				if (! class_exists($class, false))
				{
					throw new \RuntimeException(sprintf(lang('Migrations.classNotFound'), $class));
				}

				// Forcing migration to selected database group
				$instance = new $class(\Config\Database::forge($this->group));

				if (! is_callable([$instance, $direction]))
				{
					throw new \RuntimeException(sprintf(lang('Migrations.missingMethod'), $direction));
				}

				$instance->{$direction}();

				$history[] = $migration;

				$migrationStatus = true;
			}
		}

		$this->updateHistory($history, $direction, $batch);

		return $migrationStatus;
	}
}
