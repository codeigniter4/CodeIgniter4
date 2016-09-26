<?php namespace CodeIgniter\Database;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\ConfigException;

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

	/**
	 * If true, will continue instead of throwing
	 * exceptions.
	 * @var bool
	 */
	protected $silent = false;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param BaseConfig $config
	 * @param \CodeIgniter\Database\ConnectionInterface $db
	 * @throws ConfigException
	 */
	public function __construct(BaseConfig $config, ConnectionInterface $db = null)
	{
		$this->enabled        = $config->enabled        ?? false;
		$this->type           = $config->type           ?? 'timestamp';
		$this->table          = $config->table          ?? 'migrations';
		$this->currentVersion = $config->currentVersion ?? 0;
		$this->path           = $config->path           ?? APPPATH.'Database/Migrations/';

		$this->path = rtrim($this->path, '/').'/';

		if (empty($this->table))
		{
			throw new ConfigException(lang('Migrations.migMissingTable'));
		}

		if ( ! in_array($this->type, ['sequential', 'timestamp']))
		{
			throw new ConfigException(lang('Migrations.migInvalidType').$this->type);
		}

		// Migration basename regex
		$this->regex = ($this->type === 'timestamp')
			? '/^\d{14}_(\w+)$/'
			: '/^\d{3}_(\w+)$/';

		// If no db connection passed in, use
		// default database group.
		$this->db = ! empty($db)
			? $db
			: \Config\Database::connect();

		$this->ensureTable();
	}

	//--------------------------------------------------------------------

	/**
	 * Migrate to a schema version
	 *
	 * Calls each migration step required to get to the schema version of
	 * choice
	 *
	 * @param    string $targetVersion Target schema version
	 * @param $group
	 * @return mixed TRUE if no migrations are found, current version string on success, FALSE on failure
	 * @throws ConfigException
	 */
	public function version(string $targetVersion, $group='default')
	{
		if (! $this->enabled)
		{
			throw new ConfigException(lang('Migrations.migDisabled'));
		}

		// Note: We use strings, so that timestamp versions work on 32-bit systems
		$currentVersion = $this->getVersion($group);

		if ($this->type === 'sequential')
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
			throw new \RuntimeException(lang('Migrations.migNotFound').$targetVersion);
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
				throw new \RuntimeException(lang('Migration.migGap').$number);
			}

			include_once $file;
			$class = 'Migration_'.($this->getMigrationName(basename($file, '.php')));

			// Validate the migration file structure
			if ( ! class_exists($class, false))
			{
				throw new \RuntimeException(sprintf(lang('Migrations.migClassNotFound'), $class));
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
					throw new \RuntimeException(sprintf(lang('Migrations.migMissingMethod'), $method));
				}

				call_user_func([$instance, $method]);

				$currentVersion = $number;
				if ($method === 'up') $this->addHistory($currentVersion, $instance->getDBGroup());
				elseif ($method === 'down') $this->removeHistory($currentVersion, $instance->getDBGroup());
			}
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
			if ($this->silent) return false;

			throw new \RuntimeException(lang('Migrations.migNotFound'));
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
					throw new \RuntimeException(lang('Migrations.migMultiple').$number);
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
	 * Grabs the full migration history from the database.
	 *
	 * @param $group
	 * @return mixed
	 */
	public function getHistory($group = 'default')
	{
	    $query = $this->db->table($this->table)
		                ->where('group', $group)
		                ->get();

		if (! $query) return [];

		return $query->getResultArray();
	}

	//--------------------------------------------------------------------

	/**
	 * If $silent == true, then will not throw exceptions and will
	 * attempt to continue gracefully.
	 *
	 * @param bool $silent
	 *
	 * @return $this
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
	 * @param $group
	 * @return    string    Current migration version
	 */
	protected function getVersion($group = 'default')
	{
		if (empty($group))
		{
			$config = new \Config\Database();
			$group = $config->defaultGroup;
			unset($config);
		}

		$row = $this->db->table($this->table)
				->select('version')
				->where('group', $group)
				->orderBy('version', 'DESC')
				->get()
				->getRow();

		return $row ? $row->version : '0';
	}

	//--------------------------------------------------------------------

	/**
	 * Stores the current schema version.
	 *
	 * @param string $version
	 * @param string $group     The database group
	 *
	 * @internal param string $migration Migration reached
	 *
	 */
	protected function addHistory($version, $group = 'default')
	{
		if (empty($group))
		{
			$config = new \Config\Database();
			$group = $config->defaultGroup;
			unset($config);
		}

		$this->db->table($this->table)
		         ->insert([
			         'version' => $version,
			         'group'   => $group,
		             'time'    => date('Y-m-d H:i:s')
		         ]);
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a single history
	 *
	 * @param string $version
	 * @param string $group     The database group
	 */
	protected function removeHistory($version, $group = 'default')
	{
		if (empty($group))
		{
			$config = new \Config\Database();
			$group = $config->defaultGroup;
			unset($config);
		}

		$this->db->table($this->table)
				 ->where('version', $version)
				 ->where('group', $group)
				 ->delete();
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
			'version' => [
				'type' => 'BIGINT',
			    'constraint' => 20,
			    'null' => false
			],
			'group' => [
				'type' => 'varchar',
			    'constraint' => 255,
			    'null' => false
			],
			'time' => [
				'type' => 'timestamp',
			    'null' => false
			]
		]);

		$forge->createTable($this->table, true);
	}

	//--------------------------------------------------------------------

}
