<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\SQLite3;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use ErrorException;
use Exception;
use SQLite3;
use SQLite3Result;
use stdClass;

/**
 * Connection for SQLite3
 */
class Connection extends BaseConnection
{
    /**
     * Database driver
     *
     * @var string
     */
    public $DBDriver = 'SQLite3';

    /**
     * Identifier escape character
     *
     * @var string
     */
    public $escapeChar = '`';

    /**
     * @var bool Enable Foreign Key constraint or not
     */
    protected $foreignKeys = false;

    public function initialize()
    {
        parent::initialize();

        if ($this->foreignKeys) {
            $this->enableForeignKeyChecks();
        }
    }

    /**
     * Connect to the database.
     *
     * @throws DatabaseException
     *
     * @return mixed
     */
    public function connect(bool $persistent = false)
    {
        if ($persistent && $this->DBDebug) {
            throw new DatabaseException('SQLite3 doesn\'t support persistent connections.');
        }

        try {
            if ($this->database !== ':memory:' && strpos($this->database, DIRECTORY_SEPARATOR) === false) {
                $this->database = WRITEPATH . $this->database;
            }

            return (! $this->password)
                ? new SQLite3($this->database)
                : new SQLite3($this->database, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE, $this->password);
        } catch (Exception $e) {
            throw new DatabaseException('SQLite3 error: ' . $e->getMessage());
        }
    }

    /**
     * Keep or establish the connection if no queries have been sent for
     * a length of time exceeding the server's idle timeout.
     */
    public function reconnect()
    {
        $this->close();
        $this->initialize();
    }

    /**
     * Close the database connection.
     */
    protected function _close()
    {
        $this->connID->close();
    }

    /**
     * Select a specific database table to use.
     */
    public function setDatabase(string $databaseName): bool
    {
        return false;
    }

    /**
     * Returns a string containing the version of the database being used.
     */
    public function getVersion(): string
    {
        if (isset($this->dataCache['version'])) {
            return $this->dataCache['version'];
        }

        $version = SQLite3::version();

        return $this->dataCache['version'] = $version['versionString'];
    }

    /**
     * Execute the query
     *
     * @return bool|SQLite3Result
     */
    protected function execute(string $sql)
    {
        try {
            return $this->isWriteType($sql)
                ? $this->connID->exec($sql)
                : $this->connID->query($sql);
        } catch (ErrorException $e) {
            log_message('error', (string) $e);
            if ($this->DBDebug) {
                throw $e;
            }
        }

        return false;
    }

    /**
     * Returns the total number of rows affected by this query.
     */
    public function affectedRows(): int
    {
        return $this->connID->changes();
    }

    /**
     * Platform-dependant string escape
     */
    protected function _escapeString(string $str): string
    {
        if (! $this->connID) {
            $this->initialize();
        }

        return $this->connID->escapeString($str);
    }

    /**
     * Generates the SQL for listing tables in a platform-dependent manner.
     *
     * @param string|null $tableName If $tableName is provided will return only this table if exists.
     */
    protected function _listTables(bool $prefixLimit = false, ?string $tableName = null): string
    {
        if ($tableName !== null) {
            return 'SELECT "NAME" FROM "SQLITE_MASTER" WHERE "TYPE" = \'table\''
                   . ' AND "NAME" NOT LIKE \'sqlite!_%\' ESCAPE \'!\''
                   . ' AND "NAME" LIKE ' . $this->escape($tableName);
        }

        return 'SELECT "NAME" FROM "SQLITE_MASTER" WHERE "TYPE" = \'table\''
               . ' AND "NAME" NOT LIKE \'sqlite!_%\' ESCAPE \'!\''
               . (($prefixLimit !== false && $this->DBPrefix !== '')
                    ? ' AND "NAME" LIKE \'' . $this->escapeLikeString($this->DBPrefix) . '%\' ' . sprintf($this->likeEscapeStr, $this->likeEscapeChar)
                    : '');
    }

    /**
     * Generates a platform-specific query string so that the column names can be fetched.
     */
    protected function _listColumns(string $table = ''): string
    {
        return 'PRAGMA TABLE_INFO(' . $this->protectIdentifiers($table, true, null, false) . ')';
    }

    /**
     * @throws DatabaseException
     *
     * @return array|false
     */
    public function getFieldNames(string $table)
    {
        // Is there a cached result?
        if (isset($this->dataCache['field_names'][$table])) {
            return $this->dataCache['field_names'][$table];
        }

        if (empty($this->connID)) {
            $this->initialize();
        }

        $sql = $this->_listColumns($table);

        $query                                  = $this->query($sql);
        $this->dataCache['field_names'][$table] = [];

        foreach ($query->getResultArray() as $row) {
            // Do we know from where to get the column's name?
            if (! isset($key)) {
                if (isset($row['column_name'])) {
                    $key = 'column_name';
                } elseif (isset($row['COLUMN_NAME'])) {
                    $key = 'COLUMN_NAME';
                } elseif (isset($row['name'])) {
                    $key = 'name';
                } else {
                    // We have no other choice but to just get the first element's key.
                    $key = key($row);
                }
            }

            $this->dataCache['field_names'][$table][] = $row[$key];
        }

        return $this->dataCache['field_names'][$table];
    }

    /**
     * Returns an array of objects with field data
     *
     * @throws DatabaseException
     *
     * @return stdClass[]
     */
    protected function _fieldData(string $table): array
    {
        if (false === $query = $this->query('PRAGMA TABLE_INFO(' . $this->protectIdentifiers($table, true, null, false) . ')')) {
            throw new DatabaseException(lang('Database.failGetFieldData'));
        }

        $query = $query->getResultObject();

        if (empty($query)) {
            return [];
        }

        $retVal = [];

        for ($i = 0, $c = count($query); $i < $c; $i++) {
            $retVal[$i] = new stdClass();

            $retVal[$i]->name        = $query[$i]->name;
            $retVal[$i]->type        = $query[$i]->type;
            $retVal[$i]->max_length  = null;
            $retVal[$i]->default     = $query[$i]->dflt_value;
            $retVal[$i]->primary_key = isset($query[$i]->pk) && (bool) $query[$i]->pk;
            $retVal[$i]->nullable    = isset($query[$i]->notnull) && ! (bool) $query[$i]->notnull;
        }

        return $retVal;
    }

    /**
     * Returns an array of objects with index data
     *
     * @throws DatabaseException
     *
     * @return stdClass[]
     */
    protected function _indexData(string $table): array
    {
        // Get indexes
        // Don't use PRAGMA index_list, so we can preserve index order
        $sql = "SELECT name FROM sqlite_master WHERE type='index' AND tbl_name=" . $this->escape(strtolower($table));
        if (($query = $this->query($sql)) === false) {
            throw new DatabaseException(lang('Database.failGetIndexData'));
        }
        $query = $query->getResultObject();

        $retVal = [];

        foreach ($query as $row) {
            $obj = new stdClass();

            $obj->name = $row->name;

            // Get fields for index
            $obj->fields = [];

            if (false === $fields = $this->query('PRAGMA index_info(' . $this->escape(strtolower($row->name)) . ')')) {
                throw new DatabaseException(lang('Database.failGetIndexData'));
            }

            $fields = $fields->getResultObject();

            foreach ($fields as $field) {
                $obj->fields[] = $field->name;
            }

            $retVal[$obj->name] = $obj;
        }

        return $retVal;
    }

    /**
     * Returns an array of objects with Foreign key data
     *
     * @return stdClass[]
     */
    protected function _foreignKeyData(string $table): array
    {
        if ($this->supportsForeignKeys() !== true) {
            return [];
        }

        $tables = $this->listTables();

        if (empty($tables)) {
            return [];
        }

        $retVal = [];

        foreach ($tables as $table) {
            $query = $this->query("PRAGMA foreign_key_list({$table})")->getResult();

            foreach ($query as $row) {
                $obj                     = new stdClass();
                $obj->constraint_name    = $row->from . ' to ' . $row->table . '.' . $row->to;
                $obj->table_name         = $table;
                $obj->foreign_table_name = $row->table;
                $obj->sequence           = $row->seq;

                $retVal[] = $obj;
            }
        }

        return $retVal;
    }

    /**
     * Returns platform-specific SQL to disable foreign key checks.
     *
     * @return string
     */
    protected function _disableForeignKeyChecks()
    {
        return 'PRAGMA foreign_keys = OFF';
    }

    /**
     * Returns platform-specific SQL to enable foreign key checks.
     *
     * @return string
     */
    protected function _enableForeignKeyChecks()
    {
        return 'PRAGMA foreign_keys = ON';
    }

    /**
     * Returns the last error code and message.
     * Must return this format: ['code' => string|int, 'message' => string]
     * intval(code) === 0 means "no error".
     *
     * @return array<string, int|string>
     */
    public function error(): array
    {
        return [
            'code'    => $this->connID->lastErrorCode(),
            'message' => $this->connID->lastErrorMsg(),
        ];
    }

    /**
     * Insert ID
     */
    public function insertID(): int
    {
        return $this->connID->lastInsertRowID();
    }

    /**
     * Begin Transaction
     */
    protected function _transBegin(): bool
    {
        return $this->connID->exec('BEGIN TRANSACTION');
    }

    /**
     * Commit Transaction
     */
    protected function _transCommit(): bool
    {
        return $this->connID->exec('END TRANSACTION');
    }

    /**
     * Rollback Transaction
     */
    protected function _transRollback(): bool
    {
        return $this->connID->exec('ROLLBACK');
    }

    /**
     * Checks to see if the current install supports Foreign Keys
     * and has them enabled.
     */
    public function supportsForeignKeys(): bool
    {
        $result = $this->simpleQuery('PRAGMA foreign_keys');

        return (bool) $result;
    }
}
