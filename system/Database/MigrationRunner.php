<?php namespace CodeIgniter\Database;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\ConfigException;
use Config\Database;

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
	 * The location where migrations can be found.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * The pattern used to locate migration file versions.
	 *
	 * @var string
	 */
	protected $regex;

	/**
	 * The main database connection. Used to store
	 * migration information in.
	 * @var ConnectionInterface
	 */
	protected $db;

	//--------------------------------------------------------------------

	public function __construct(BaseConfig $config, ConnectionInterface $db = null)
	{
		$this->enabled        = $config->enabled        ?? false;
		$this->type           = $config->type           ?? 'timestamp';
		$this->table          = $config->table          ?? 'migrations';
		$this->currentVersion = $config->currentVersion ?? 0;
		$this->path           = $config->path           ?? APPPATH.'Database/Migrations/';

		$this->path = rtrim($this->path, '/').'/';

		if ($this->enabled !== true)
		{
			throw new ConfigException('Migrations have been loaded but are disabled or setup incorrectly.');
		}

		if (empty($this->table))
		{
			throw new ConfigException('Migrations table must be set.');
		}

		if ( ! in_array($this->type, ['sequential', 'timestamp']))
		{
			throw new ConfigException('An invalid migration numbering type was specified: '.$this->type);
		}

		// Migration basename regex
		$this->regex = ($this->type === 'timestamp')
			? '/^\d{14}_(\w+)$/'
			: '/^\d{3}_(\w+)$/';

		// If no db connection passed in, use
		// default database group.
		$this->db =& ! empty($db)
			? $db
			: Database::connect();
	}

	//--------------------------------------------------------------------

	/**
	 * Migrate to a schema version
	 *
	 * Calls each migration step required to get to the schema version of
	 * choice
	 *
	 * @param    string $targetVersion Target schema version
	 *
	 * @return    mixed    TRUE if no migrations are found, current version string on success, FALSE on failure
	 */
	public function version($targetVersion)
	{
		// Note: We use strings, so that timestamp versions work on 32-bit systems
		$currentVersion = $this->getVersion();

		if ($this->_migration_type === 'sequential')
		{
			$targetVersion = sprintf('%03d', $targetVersion);
		}
		else
		{
			$targetVersion = (string)$targetVersion;
		}

		$migrations = $this->findMigrations();

		if ($targetVersion > 0 && ! isset($migrations[$targetVersion]))
		{
			throw new \RuntimeException('Migration file not found: '.$targetVersion);
		}

		if ($targetVersion > $currentVersion)
		{
			// Moving Up
			$method = 'up';
		}
		else
		{
			// Moving Down, apply in reverse order
			$method = 'down';
			krsort($migrations);
		}

		if (empty($migrations))
		{
			return true;
		}

		$previous = false;

		// Validate all available migrations, and run the ones within our target range
		foreach ($migrations as $number => $file)
		{
			// Check for sequence gaps
			if ($this->type === 'sequential' && $previous !== false && abs($number - $previous) > 1)
			{
				throw new \RuntimeException('There is a gap in the migration sequence near version number: '.$number);
			}

			include_once($file);
			$class = 'Migration_'.($this->getMigrationName(basename($file, '.php')));

			// Validate the migration file structure
			if ( ! class_exists($class, false))
			{
				throw new \RuntimeException(sprintf('The migration class "%s" could not be found.', $class));
			}

			$previous = $number;

			// Run migrations that are inside the target range
			if (
				($method === 'up' && $number > $currentVersion && $number <= $targetVersion) OR
				($method === 'down' && $number <= $currentVersion && $number > $targetVersion)
			)
			{
				$instance = new $class();

				if ( ! is_callable([$instance, $method]))
				{
					throw new \RuntimeException("The migration class is missing an \"{$method}\" method.");
				}

				call_user_func([$instance, $method]);

				$currentVersion = $number;
				$this->updateVersion($currentVersion);
			}
		}

		// This is necessary when moving down, since the the last migration applied
		// will be the down() method for the next migration up from the target
		if ($currentVersion <> $targetVersion)
		{
			$currentVersion = $targetVersion;
			$this->updateVersion($currentVersion);
		}

		return $currentVersion;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the schema to the latest migration
	 *
	 * @return    mixed    Current version string on success, FALSE on failure
	 */
	public function latest()
	{
		$migrations = $this->findMigrations();

		if (empty($migrations))
		{
			throw new \RuntimeException('No migrations were found.');
		}

		$lastMigration = basename(end($migrations));

		// Calculate the last migration step from existing migration
		// filenames and proceed to the standard version migration
		return $this->version($this->getMigrationNumber($lastMigration));
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the schema to the migration version set in config
	 *
	 * @return    mixed    TRUE if no migrations are found, current version string on success, FALSE on failure
	 */
	public function current()
	{
		return $this->version($this->currentVersion);
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves list of available migration scripts
	 *
	 * @return    array    list of migration file paths sorted by version
	 */
	public function findMigrations()
	{
		$migrations = [];

		// Load all *_*.php files in the migrations path
		foreach (glob($this->path.'*_*.php') as $file)
		{
			$name = basename($file, '.php');

			// Filter out non-migration files
			if (preg_match($this->regex, $name))
			{
				$number = $this->getMigrationNumber($name);

				// There cannot be duplicate migration numbers
				if (isset($migrations[$number]))
				{
					throw new \RuntimeException('There are multiple migrations with the same version number: '.$number);
				}

				$migrations[$number] = $file;
			}
		}

		ksort($migrations);

		return $migrations;
	}

	//--------------------------------------------------------------------

	/**
	 * Updates the expected location of the migration files.
	 * Allows other scripts to modify on the fly as needed.
	 *
	 * @param string $path
	 *
	 * @return $this
	 */
	public function setPath(string $path)
	{
	    $this->path = rtrim($path, '/').'/';

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
		return sscanf($migration, '%[0-9]+', $number)
			? $number : '0';
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
		                ->get()
		                ->getRow();

		return $row ? $row->version : '0';
	}

	//--------------------------------------------------------------------

	/**
	 * Stores the current schema version.
	 *
	 * @param    string $migration Migration reached
	 *
	 * @return    void
	 */
	protected function updateVersion($migration)
	{
		$this->db->table($this->table)
		         ->update([
			         'version' => $migration,
		         ]);
	}

	//--------------------------------------------------------------------

}
