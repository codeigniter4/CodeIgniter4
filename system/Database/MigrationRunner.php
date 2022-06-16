<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\ConfigException;
use Config\Database;
use Config\Migrations as MigrationsConfig;
use Config\Services;
use RuntimeException;
use stdClass;

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
     * Name of table to store meta information
     *
     * @var string
     */
    protected $table;

    /**
     * The Namespace where migrations can be found.
     * `null` is all namespaces.
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
     * @var BaseConnection
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
     * @var array
     */
    protected $cliMessages = [];

    /**
     * Tracks whether we have already ensured
     * the table exists or not.
     *
     * @var bool
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
     * @var bool
     */
    protected $groupSkip = false;

    /**
     * Constructor.
     *
     * When passing in $db, you may pass any of the following to connect:
     * - group name
     * - existing connection instance
     * - array of database configuration values
     *
     * @param array|ConnectionInterface|string|null $db
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

    /**
     * Locate and run all new migrations
     *
     * @throws ConfigException
     * @throws RuntimeException
     *
     * @return bool
     */
    public function latest(?string $group = null)
    {
        if (! $this->enabled) {
            throw ConfigException::forDisabledMigrations();
        }

        $this->ensureTable();

        if ($group !== null) {
            $this->groupFilter = $group;
            $this->setGroup($group);
        }

        $migrations = $this->findMigrations();

        if (empty($migrations)) {
            return true;
        }

        foreach ($this->getHistory((string) $group) as $history) {
            unset($migrations[$this->getObjectUid($history)]);
        }

        $batch = $this->getLastBatch() + 1;

        foreach ($migrations as $migration) {
            if ($this->migrate('up', $migration)) {
                if ($this->groupSkip === true) {
                    $this->groupSkip = false;

                    continue;
                }

                $this->addHistory($migration, $batch);
            } else {
                $this->regress(-1);

                $message = lang('Migrations.generalFault');

                if ($this->silent) {
                    $this->cliMessages[] = "\t" . CLI::color($message, 'red');

                    return false;
                }

                throw new RuntimeException($message);
            }
        }

        $data           = get_object_vars($this);
        $data['method'] = 'latest';
        Events::trigger('migrate', $data);

        return true;
    }

    /**
     * Migrate down to a previous batch
     *
     * Calls each migration step required to get to the provided batch
     *
     * @param int $targetBatch Target batch number, or negative for a relative batch, 0 for all
     *
     * @throws ConfigException
     * @throws RuntimeException
     *
     * @return mixed Current batch number on success, FALSE on failure or no migrations are found
     */
    public function regress(int $targetBatch = 0, ?string $group = null)
    {
        if (! $this->enabled) {
            throw ConfigException::forDisabledMigrations();
        }

        // Set database group if not null
        if ($group !== null) {
            $this->setGroup($group);
        }

        $this->ensureTable();

        $batches = $this->getBatches();

        if ($targetBatch < 0) {
            $targetBatch = $batches[count($batches) - 1 + $targetBatch] ?? 0;
        }

        if (empty($batches) && $targetBatch === 0) {
            return true;
        }

        if ($targetBatch !== 0 && ! in_array($targetBatch, $batches, true)) {
            $message = lang('Migrations.batchNotFound') . $targetBatch;

            if ($this->silent) {
                $this->cliMessages[] = "\t" . CLI::color($message, 'red');

                return false;
            }

            throw new RuntimeException($message);
        }

        $tmpNamespace = $this->namespace;

        $this->namespace = null;
        $allMigrations   = $this->findMigrations();

        $migrations = [];

        while ($batch = array_pop($batches)) {
            if ($batch <= $targetBatch) {
                break;
            }

            foreach ($this->getBatchHistory($batch, 'desc') as $history) {
                $uid = $this->getObjectUid($history);

                if (! isset($allMigrations[$uid])) {
                    $message = lang('Migrations.gap') . ' ' . $history->version;

                    if ($this->silent) {
                        $this->cliMessages[] = "\t" . CLI::color($message, 'red');

                        return false;
                    }

                    throw new RuntimeException($message);
                }

                $migration          = $allMigrations[$uid];
                $migration->history = $history;
                $migrations[]       = $migration;
            }
        }

        foreach ($migrations as $migration) {
            if ($this->migrate('down', $migration)) {
                $this->removeHistory($migration->history);
            } else {
                $message = lang('Migrations.generalFault');

                if ($this->silent) {
                    $this->cliMessages[] = "\t" . CLI::color($message, 'red');

                    return false;
                }

                throw new RuntimeException($message);
            }
        }

        $data           = get_object_vars($this);
        $data['method'] = 'regress';
        Events::trigger('migrate', $data);

        $this->namespace = $tmpNamespace;

        return true;
    }

    /**
     * Migrate a single file regardless of order or batches.
     * Method "up" or "down" determined by presence in history.
     * NOTE: This is not recommended and provided mostly for testing.
     *
     * @param string $path Full path to a valid migration file
     * @param string $path Namespace of the target migration
     */
    public function force(string $path, string $namespace, ?string $group = null)
    {
        if (! $this->enabled) {
            throw ConfigException::forDisabledMigrations();
        }

        $this->ensureTable();

        if ($group !== null) {
            $this->groupFilter = $group;
            $this->setGroup($group);
        }

        $migration = $this->migrationFromFile($path, $namespace);
        if (empty($migration)) {
            $message = lang('Migrations.notFound');

            if ($this->silent) {
                $this->cliMessages[] = "\t" . CLI::color($message, 'red');

                return false;
            }

            throw new RuntimeException($message);
        }

        $method = 'up';
        $this->setNamespace($migration->namespace);

        foreach ($this->getHistory($this->group) as $history) {
            if ($this->getObjectUid($history) === $migration->uid) {
                $method             = 'down';
                $migration->history = $history;
                break;
            }
        }

        if ($method === 'up') {
            $batch = $this->getLastBatch() + 1;

            if ($this->migrate('up', $migration) && $this->groupSkip === false) {
                $this->addHistory($migration, $batch);

                return true;
            }

            $this->groupSkip = false;
        } elseif ($this->migrate('down', $migration)) {
            $this->removeHistory($migration->history);

            return true;
        }

        $message = lang('Migrations.generalFault');

        if ($this->silent) {
            $this->cliMessages[] = "\t" . CLI::color($message, 'red');

            return false;
        }

        throw new RuntimeException($message);
    }

    /**
     * Retrieves list of available migration scripts
     *
     * @return array List of all located migrations by their UID
     */
    public function findMigrations(): array
    {
        $namespaces = $this->namespace ? [$this->namespace] : array_keys(Services::autoloader()->getNamespace());
        $migrations = [];

        foreach ($namespaces as $namespace) {
            if (ENVIRONMENT !== 'testing' && $namespace === 'Tests\Support') {
                continue;
            }

            foreach ($this->findNamespaceMigrations($namespace) as $migration) {
                $migrations[$migration->uid] = $migration;
            }
        }

        // Sort migrations ascending by their UID (version)
        ksort($migrations);

        return $migrations;
    }

    /**
     * Retrieves a list of available migration scripts for one namespace
     */
    public function findNamespaceMigrations(string $namespace): array
    {
        $migrations = [];
        $locator    = Services::locator(true);

        if (! empty($this->path)) {
            helper('filesystem');
            $dir   = rtrim($this->path, DIRECTORY_SEPARATOR) . '/';
            $files = get_filenames($dir, true, false, false);
        } else {
            $files = $locator->listNamespaceFiles($namespace, '/Database/Migrations/');
        }

        foreach ($files as $file) {
            $file = empty($this->path) ? $file : $this->path . str_replace($this->path, '', $file);

            if ($migration = $this->migrationFromFile($file, $namespace)) {
                $migrations[] = $migration;
            }
        }

        return $migrations;
    }

    /**
     * Create a migration object from a file path.
     *
     * @return false|object Returns the migration object, or false on failure
     */
    protected function migrationFromFile(string $path, string $namespace)
    {
        if (substr($path, -4) !== '.php') {
            return false;
        }

        $name = basename($path, '.php');

        if (! preg_match($this->regex, $name)) {
            return false;
        }

        $locator = Services::locator(true);

        $migration = new stdClass();

        $migration->version   = $this->getMigrationNumber($name);
        $migration->name      = $this->getMigrationName($name);
        $migration->path      = $path;
        $migration->class     = $locator->getClassname($path);
        $migration->namespace = $namespace;
        $migration->uid       = $this->getObjectUid($migration);

        return $migration;
    }

    /**
     * Allows other scripts to modify on the fly as needed.
     *
     * @return MigrationRunner
     */
    public function setNamespace(?string $namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Allows other scripts to modify on the fly as needed.
     *
     * @return MigrationRunner
     */
    public function setGroup(string $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return MigrationRunner
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * If $silent == true, then will not throw exceptions and will
     * attempt to continue gracefully.
     *
     * @return MigrationRunner
     */
    public function setSilent(bool $silent)
    {
        $this->silent = $silent;

        return $this;
    }

    /**
     * Extracts the migration number from a filename
     */
    protected function getMigrationNumber(string $migration): string
    {
        preg_match('/^\d{4}[_-]?\d{2}[_-]?\d{2}[_-]?\d{6}/', $migration, $matches);

        return count($matches) ? $matches[0] : '0';
    }

    /**
     * Extracts the migration class name from a filename
     */
    protected function getMigrationName(string $migration): string
    {
        $parts = explode('_', $migration);
        array_shift($parts);

        return implode('_', $parts);
    }

    /**
     * Uses the non-repeatable portions of a migration or history
     * to create a sortable unique key
     *
     * @param object $object migration or $history
     */
    public function getObjectUid($object): string
    {
        return preg_replace('/[^0-9]/', '', $object->version) . $object->class;
    }

    /**
     * Retrieves messages formatted for CLI output
     */
    public function getCliMessages(): array
    {
        return $this->cliMessages;
    }

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

    /**
     * Truncates the history table.
     */
    public function clearHistory()
    {
        if ($this->db->tableExists($this->table)) {
            $this->db->table($this->table)->truncate();
        }
    }

    /**
     * Add a history to the table.
     *
     * @param object $migration
     */
    protected function addHistory($migration, int $batch)
    {
        $this->db->table($this->table)->insert([
            'version'   => $migration->version,
            'class'     => $migration->class,
            'group'     => $this->group,
            'namespace' => $migration->namespace,
            'time'      => time(),
            'batch'     => $batch,
        ]);

        if (is_cli()) {
            $this->cliMessages[] = sprintf(
                "\t%s(%s) %s_%s",
                CLI::color(lang('Migrations.added'), 'yellow'),
                $migration->namespace,
                $migration->version,
                $migration->class
            );
        }
    }

    /**
     * Removes a single history
     *
     * @param object $history
     */
    protected function removeHistory($history)
    {
        $this->db->table($this->table)->where('id', $history->id)->delete();

        if (is_cli()) {
            $this->cliMessages[] = sprintf(
                "\t%s(%s) %s_%s",
                CLI::color(lang('Migrations.removed'), 'yellow'),
                $history->namespace,
                $history->version,
                $history->class
            );
        }
    }

    /**
     * Grabs the full migration history from the database for a group
     */
    public function getHistory(string $group = 'default'): array
    {
        $this->ensureTable();

        $builder = $this->db->table($this->table);

        // If group was specified then use it
        if (! empty($group)) {
            $builder->where('group', $group);
        }

        // If a namespace was specified then use it
        if ($this->namespace) {
            $builder->where('namespace', $this->namespace);
        }

        $query = $builder->orderBy('id', 'ASC')->get();

        return ! empty($query) ? $query->getResultObject() : [];
    }

    /**
     * Returns the migration history for a single batch.
     *
     * @param string $order
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

    /**
     * Returns all the batches from the database history in order
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

        return array_map('intval', array_column($batches, 'batch'));
    }

    /**
     * Returns the value of the last batch in the database.
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

        return (int) $batch;
    }

    /**
     * Returns the version number of the first migration for a batch.
     * Mostly just for tests.
     */
    public function getBatchStart(int $batch): string
    {
        if ($batch < 0) {
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

    /**
     * Returns the version number of the last migration for a batch.
     * Mostly just for tests.
     */
    public function getBatchEnd(int $batch): string
    {
        if ($batch < 0) {
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

    /**
     * Ensures that we have created our migrations table
     * in the database.
     */
    public function ensureTable()
    {
        if ($this->tableChecked || $this->db->tableExists($this->table)) {
            return;
        }

        $forge = Database::forge($this->db);

        $forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'version' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'class' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'group' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'namespace' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'time' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'batch' => [
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
     */
    protected function migrate($direction, $migration): bool
    {
        include_once $migration->path;

        $class = $migration->class;
        $this->setName($migration->name);

        // Validate the migration file structure
        if (! class_exists($class, false)) {
            $message = sprintf(lang('Migrations.classNotFound'), $class);

            if ($this->silent) {
                $this->cliMessages[] = "\t" . CLI::color($message, 'red');

                return false;
            }

            throw new RuntimeException($message);
        }

        $instance = new $class();
        $group    = $instance->getDBGroup() ?? config('Database')->defaultGroup;

        if (ENVIRONMENT !== 'testing' && $group === 'tests' && $this->groupFilter !== 'tests') {
            // @codeCoverageIgnoreStart
            $this->groupSkip = true;

            return true;
            // @codeCoverageIgnoreEnd
        }

        if ($direction === 'up' && $this->groupFilter !== null && $this->groupFilter !== $group) {
            $this->groupSkip = true;

            return true;
        }

        $this->setGroup($group);

        if (! is_callable([$instance, $direction])) {
            $message = sprintf(lang('Migrations.missingMethod'), $direction);

            if ($this->silent) {
                $this->cliMessages[] = "\t" . CLI::color($message, 'red');

                return false;
            }

            throw new RuntimeException($message);
        }

        $instance->{$direction}();

        return true;
    }
}
