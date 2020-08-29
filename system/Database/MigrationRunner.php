<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
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
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Database;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Exceptions\ConfigException;
use Config\Migrations as MigrationsConfig;
use Config\Services;

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
	 * @var string|null
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

	/**
	 * The database Group filter.
	 *
	 * @var string|null
	 */
	protected $groupFilter;

	/**
	 * Used to skip current migration.
	 *
	 * @var boolean
	 */
	protected $groupSkip = false;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * When passing in $db, you may pass any of the following to connect:
	 * - group name
	 * - existing connection instance
	 * - array of database configuration values
	 *
	 * @param MigrationsConfig                                       $config
	 * @param \CodeIgniter\Database\ConnectionInterface|array|string $db
	 *
	 * @throws ConfigException
	 */
	public function __construct(MigrationsConfig $config, $db = null)
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
	 * Locate and run all new migrations
	 *
	 * @param string|null $group
	 */
	public function latest(string $group = null)
	{
		if (! $this->enabled)
		{
			throw ConfigException::forDisabledMigrations();
		}

		$this->ensureTable();

		// Set database group if not null
		if (! is_null($group))
		{
			$this->groupFilter = $group;
			$this->setGroup($group);
		}

		// Locate the migrations
		$migrations = $this->findMigrations();

		// If nothing was found then we're done
		if (empty($migrations))
		{
			return true;
		}

		// Remove any migrations already in the history
		foreach ($this->getHistory($this->group) as $history)
		{
			unset($migrations[$this->getObjectUid($history)]);
		}

		// Start a new batch
		$batch = $this->getLastBatch() + 1;

		// Run each migration
		foreach ($migrations as $migration)
		{
			if ($this->migrate('up', $migration))
			{
				if ($this->groupSkip === true)
				{
					$this->groupSkip = false;
					continue;
				}

				$this->addHistory($migration, $batch);
			}
			// If a migration failed then try to back out what was done
			else
			{
				$this->regress(-1);

				$message = lang('Migrations.generalFault');

				if ($this->silent)
				{
					$this->cliMessages[] = "\t" . CLI::color($message, 'red');
					return false;
				}
				throw new \RuntimeException($message);
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Migrate down to a previous batch
	 *
	 * Calls each migration step required to get to the provided batch
	 *
	 * @param integer     $targetBatch Target batch number, or negative for a relative batch, 0 for all
	 * @param string|null $group
	 *
	 * @return mixed Current batch number on success, FALSE on failure or no migrations are found
	 * @throws ConfigException
	 */
	public function regress(int $targetBatch = 0, string $group = null)
	{
		if (! $this->enabled)
		{
			throw ConfigException::forDisabledMigrations();
		}

		// Set database group if not null
		if (! is_null($group))
		{
			$this->setGroup($group);
		}

		$this->ensureTable();

		// Get all the batches
		$batches = $this->getBatches();

		// Convert a relative batch to its absolute
		if ($targetBatch < 0)
		{
			$targetBatch = $batches[count($batches) - 1 + $targetBatch] ?? 0;
		}

		// If the goal was rollback then check if it is done
		if (empty($batches) && $targetBatch === 0)
		{
			return true;
		}

		// Make sure $targetBatch is found
		if ($targetBatch !== 0 && ! in_array($targetBatch, $batches, true))
		{
			$message = lang('Migrations.batchNotFound') . $targetBatch;

			if ($this->silent)
			{
				$this->cliMessages[] = "\t" . CLI::color($message, 'red');
				return false;
			}
			throw new \RuntimeException($message);
		}

		// Save the namespace to restore it after loading migrations
		$tmpNamespace = $this->namespace;

		// Get all migrations
		$this->namespace = null;
		$allMigrations   = $this->findMigrations();

		// Gather migrations down through each batch until reaching the target
		$migrations = [];
		while ($batch = array_pop($batches))
		{
			// Check if reached target
			if ($batch <= $targetBatch)
			{
				break;
			}

			// Get the migrations from each history
			foreach ($this->getBatchHistory($batch, 'desc') as $history)
			{
				// Create a UID from the history to match its migration
				$uid = $this->getObjectUid($history);

				// Make sure the migration is still available
				if (! isset($allMigrations[$uid]))
				{
					$message = lang('Migrations.gap') . ' ' . $history->version;

					if ($this->silent)
					{
						$this->cliMessages[] = "\t" . CLI::color($message, 'red');
						return false;
					}
					throw new \RuntimeException($message);
				}

				// Add the history and put it on the list
				$migration          = $allMigrations[$uid];
				$migration->history = $history;
				$migrations[]       = $migration;
			}
		}

		// Run each migration
		foreach ($migrations as $migration)
		{
			if ($this->migrate('down', $migration))
			{
				$this->removeHistory($migration->history);
			}
			// If a migration failed then quit so as not to ruin the whole batch
			else
			{
				$message = lang('Migrations.generalFault');

				if ($this->silent)
				{
					$this->cliMessages[] = "\t" . CLI::color($message, 'red');
					return false;
				}
				throw new \RuntimeException($message);
			}
		}

		// Restore the namespace
		$this->namespace = $tmpNamespace;

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Migrate a single file regardless of order or batches.
	 * Method "up" or "down" determined by presence in history.
	 * NOTE: This is not recommended and provided mostly for testing.
	 *
	 * @param string      $path  Full path to a valid migration file
	 * @param string      $path  Namespace of the target migration
	 * @param string|null $group
	 */
	public function force(string $path, string $namespace, string $group = null)
	{
		if (! $this->enabled)
		{
			throw ConfigException::forDisabledMigrations();
		}

		$this->ensureTable();

		// Set database group if not null
		if (! is_null($group))
		{
			$this->groupFilter = $group;
			$this->setGroup($group);
		}

		// Create and validate the migration
		$migration = $this->migrationFromFile($path, $namespace);
		if (empty($migration))
		{
			$message = lang('Migrations.notFound');

			if ($this->silent)
			{
				$this->cliMessages[] = "\t" . CLI::color($message, 'red');
				return false;
			}
			throw new \RuntimeException($message);
		}

		// Check the history for a match
		$method = 'up';
		$this->setNamespace($migration->namespace);
		foreach ($this->getHistory($this->group) as $history)
		{
			if ($this->getObjectUid($history) === $migration->uid)
			{
				$method             = 'down';
				$migration->history = $history;
				break;
			}
		}

		// up
		if ($method === 'up')
		{
			// Start a new batch
			$batch = $this->getLastBatch() + 1;

			if ($this->migrate('up', $migration) && $this->groupSkip === false)
			{
				$this->addHistory($migration, $batch);
				return true;
			}

			$this->groupSkip = false;
		}

		// down
		elseif ($this->migrate('down', $migration))
		{
			$this->removeHistory($migration->history);
			return true;
		}

		// If it came this far the migration failed
		$message = lang('Migrations.generalFault');

		if ($this->silent)
		{
			$this->cliMessages[] = "\t" . CLI::color($message, 'red');
			return false;
		}
		throw new \RuntimeException($message);
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves list of available migration scripts
	 *
	 * @return array    List of all located migrations by their UID
	 */
	public function findMigrations(): array
	{
		// If a namespace is set then use it, otherwise load all namespaces from the autoloader
		$namespaces = $this->namespace ? [$this->namespace] : array_keys(Services::autoloader()->getNamespace());

		// Collect the migrations to run by their sortable UID
		$migrations = [];
		foreach ($namespaces as $namespace)
		{
			foreach ($this->findNamespaceMigrations($namespace) as $migration)
			{
				$migrations[$migration->uid] = $migration;
			}
		}

		// Sort migrations ascending by their UID (version)
		ksort($migrations);

		return $migrations;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves a list of available migration scripts for one namespace
	 *
	 * @param string $namespace The namespace to search for migrations
	 *
	 * @return array    List of unsorted migrations from the namespace
	 */
	public function findNamespaceMigrations(string $namespace): array
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
			$files = $locator->listNamespaceFiles($namespace, '/Database/Migrations/');
		}

		// Load all *_*.php files in the migrations path
		// We can't use glob if we want it to be testable....
		foreach ($files as $file)
		{
			// Clean up the file path
			$file = empty($this->path) ? $file : $this->path . str_replace($this->path, '', $file);

			// Create the migration object from the file and save it
			if ($migration = $this->migrationFromFile($file, $namespace))
			{
				$migrations[] = $migration;
			}
		}

		return $migrations;
	}

	//--------------------------------------------------------------------

	/**
	 * Create a migration object from a file path.
	 *
	 * @param string $path The path to the file
	 * @param string $path The namespace of the target migration
	 *
	 * @return object|false    Returns the migration object, or false on failure
	 */
	protected function migrationFromFile(string $path, string $namespace)
	{
		if (substr($path, -4) !== '.php')
		{
			return false;
		}

		// Remove the extension
		$name = basename($path, '.php');

		// Filter out non-migration files
		if (! preg_match($this->regex, $name))
		{
			return false;
		}

		$locator = Services::locator(true);

		// Create migration object using stdClass
		$migration = new \stdClass();

		// Get migration version number
		$migration->version   = $this->getMigrationNumber($name);
		$migration->name      = $this->getMigrationName($name);
		$migration->path      = $path;
		$migration->class     = $locator->getClassname($path);
		$migration->namespace = $namespace;
		$migration->uid       = $this->getObjectUid($migration);

		return $migration;
	}

	//--------------------------------------------------------------------

	/**
	 * Set namespace.
	 * Allows other scripts to modify on the fly as needed.
	 *
	 * @param string $namespace or null for "all"
	 *
	 * @return MigrationRunner
	 */
	public function setNamespace(?string $namespace)
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
	 * Uses the non-repeatable portions of a migration or history
	 * to create a sortable unique key
	 *
	 * @param object $object migration or $history
	 *
	 * @return string
	 */
	public function getObjectUid($object): string
	{
		return preg_replace('/[^0-9]/', '', $object->version) . $object->class;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves messages formatted for CLI output
	 *
	 * @return array    Current migration version
	 */
	public function getCliMessages(): array
	{
		return $this->cliMessages;
	}

	//--------------------------------------------------------------------

	/**
	 * Clears any CLI messages.
	 *
	 * @return MigrationRunner
	 */
	public function clearCliMessages()
	{
		$this->cliMessages = [];

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Truncates the history table.
	 *
	 * @return void
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
	 * Add a history to the table.
	 *
	 * @param object  $migration
	 * @param integer $batch
	 *
	 * @return void
	 */
	protected function addHistory($migration, int $batch)
	{
		$this->db->table($this->table)
				 ->insert([
					 'version'   => $migration->version,
					 'class'     => $migration->class,
					 'group'     => $this->group,
					 'namespace' => $migration->namespace,
					 'time'      => time(),
					 'batch'     => $batch,
				 ]);

		if (is_cli())
		{
			$this->cliMessages[] = "\t" . CLI::color(lang('Migrations.added'),
					'yellow') . "($migration->namespace) " . $migration->version . '_' . $migration->class;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a single history
	 *
	 * @param object $history
	 *
	 * @return void
	 */
	protected function removeHistory($history)
	{
		$this->db->table($this->table)->where('id', $history->id)->delete();

		if (is_cli())
		{
			$this->cliMessages[] = "\t" . CLI::color(lang('Migrations.removed'),
					'yellow') . "($history->namespace) " . $history->version . '_' . $this->getMigrationName($history->class);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Grabs the full migration history from the database for a group
	 *
	 * @param string $group
	 *
	 * @return array
	 */
	public function getHistory(string $group = 'default'): array
	{
		$this->ensureTable();

		$criteria = ['group' => $group];

		// If a namespace was specified then use it
		if ($this->namespace)
		{
			$criteria['namespace'] = $this->namespace;
		}

		$query = $this->db->table($this->table)
						  ->where($criteria)
						  ->orderBy('id', 'ASC')
						  ->get();

		return ! empty($query) ? $query->getResultObject() : [];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the migration history for a single batch.
	 *
	 * @param integer $batch
	 *
	 * @return array
	 */
	public function getBatchHistory(int $batch, $order = 'asc'): array
	{
		$this->ensureTable();

		$query = $this->db->table($this->table)
						  ->where('batch', $batch)
						  ->orderBy('id', $order)
						  ->get();

		return ! empty($query) ? $query->getResultObject() : [];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns all the batches from the database history in order
	 *
	 * @return array
	 */
	public function getBatches(): array
	{
		$this->ensureTable();

		$batches = $this->db->table($this->table)
						  ->select('batch')
						  ->distinct()
						  ->orderBy('batch', 'asc')
						  ->get()
						  ->getResultArray();

		return array_column($batches, 'batch');
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
	 * Mostly just for tests.
	 *
	 * @param integer $batch
	 *
	 * @return string
	 */
	public function getBatchStart(int $batch): string
	{
		// Convert a relative batch to its absolute
		if ($batch < 0)
		{
			$batches = $this->getBatches();
			$batch   = $batches[count($batches) - 1] ?? 0;
		}

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
	 * Mostly just for tests.
	 *
	 * @param integer $batch
	 *
	 * @return string
	 */
	public function getBatchEnd(int $batch): string
	{
		// Convert a relative batch to its absolute
		if ($batch < 0)
		{
			$batches = $this->getBatches();
			$batch   = $batches[count($batches) - 1] ?? 0;
		}

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
				'type'           => 'BIGINT',
				'constraint'     => 20,
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
				'unsigned'   => true,
				'null'       => false,
			],
		]);

		$forge->addPrimaryKey('id');
		$forge->createTable($this->table, true);

		$this->tableChecked = true;
	}

	/**
	 * Handles the actual running of a migration.
	 *
	 * @param string $direction "up" or "down"
	 * @param object $migration The migration to run
	 *
	 * @return boolean
	 */
	protected function migrate($direction, $migration): bool
	{
		include_once $migration->path;

		$class = $migration->class;
		$this->setName($migration->name);

		// Validate the migration file structure
		if (! class_exists($class, false))
		{
			$message = sprintf(lang('Migrations.classNotFound'), $class);

			if ($this->silent)
			{
				$this->cliMessages[] = "\t" . CLI::color($message, 'red');
				return false;
			}
			throw new \RuntimeException($message);
		}

		// Initialize migration
		$instance = new $class();
		// Determine DBGroup to use
		$group = $instance->getDBGroup() ?? config('Database')->defaultGroup;

		// Skip tests db group when not running in testing environment
		if (ENVIRONMENT !== 'testing' && $group === 'tests' && $this->groupFilter !== 'tests')
		{
			// @codeCoverageIgnoreStart
			$this->groupSkip = true;
			return true;
			// @codeCoverageIgnoreEnd
		}

		// Skip migration if group filtering was set
		if ($direction === 'up' && ! is_null($this->groupFilter) && $this->groupFilter !== $group)
		{
			$this->groupSkip = true;
			return true;
		}

		$this->setGroup($group);

		if (! is_callable([$instance, $direction]))
		{
			$message = sprintf(lang('Migrations.missingMethod'), $direction);

			if ($this->silent)
			{
				$this->cliMessages[] = "\t" . CLI::color($message, 'red');
				return false;
			}
			throw new \RuntimeException($message);
		}

		$instance->{$direction}();

		return true;
	}
}
