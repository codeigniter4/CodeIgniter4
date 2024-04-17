<?php

declare(strict_types=1);

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
use Exception;
use SQLite3;
use SQLite3Result;
use stdClass;

/**
 * Connection for SQLite3
 *
 * @extends BaseConnection<SQLite3, SQLite3Result>
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

    /**
     * The milliseconds to sleep
     *
     * @var int|null milliseconds
     *
     * @see https://www.php.net/manual/en/sqlite3.busytimeout
     */
    protected $busyTimeout;

    public function initialize()
    {
        parent::initialize();

        if ($this->foreignKeys) {
            $this->enableForeignKeyChecks();
        }

        if (is_int($this->busyTimeout)) {
            $this->connID->busyTimeout($this->busyTimeout);
        }
    }

    /**
     * Connect to the database.
     *
     * @return SQLite3
     *
     * @throws DatabaseException
     */
    public function connect(bool $persistent = false)
    {
        if ($persistent && $this->DBDebug) {
            throw new DatabaseException('SQLite3 doesn\'t support persistent connections.');
        }

        try {
            if ($this->database !== ':memory:' && ! str_contains($this->database, DIRECTORY_SEPARATOR)) {
                $this->database = WRITEPATH . $this->database;
            }

            $sqlite = (! $this->password)
                ? new SQLite3($this->database)
                : new SQLite3($this->database, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE, $this->password);

            $sqlite->enableExceptions(true);

            return $sqlite;
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
     *
     * @return void
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
     * @return false|SQLite3Result
     */
    protected function execute(string $sql)
    {
        try {
            return $this->isWriteType($sql)
                ? $this->connID->exec($sql)
                : $this->connID->query($sql);
        } catch (Exception $e) {
            log_message('error', (string) $e);

            if ($this->DBDebug) {
                throw new DatabaseException($e->getMessage(), $e->getCode(), $e);
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
        if (! $this->connID instanceof SQLite3) {
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
     * @return array|false
     *
     * @throws DatabaseException
     */
    public function getFieldNames(string $table)
    {
        // Is there a cached result?
        if (isset($this->dataCache['field_names'][$table])) {
            return $this->dataCache['field_names'][$table];
        }

        if (! $this->connID instanceof SQLite3) {
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
     * @return list<stdClass>
     *
     * @throws DatabaseException
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

            $retVal[$i]->name       = $query[$i]->name;
            $retVal[$i]->type       = $query[$i]->type;
            $retVal[$i]->max_length = null;
            $retVal[$i]->nullable   = isset($query[$i]->notnull) && ! (bool) $query[$i]->notnull;
            $retVal[$i]->default    = $query[$i]->dflt_value;
            // "pk" (either zero for columns that are not part of the primary key,
            // or the 1-based index of the column within the primary key).
            // https://www.sqlite.org/pragma.html#pragma_table_info
            $retVal[$i]->primary_key = ($query[$i]->pk === 0) ? 0 : 1;
        }

        return $retVal;
    }

    /**
     * Returns an array of objects with index data
     *
     * @return array<string, stdClass>
     *
     * @throws DatabaseException
     */
    protected function _indexData(string $table): array
    {
        $sql = "SELECT 'PRIMARY' as indexname, l.name as fieldname, 'PRIMARY' as indextype
                FROM pragma_table_info(" . $this->escape(strtolower($table)) . ") as l
                WHERE l.pk <> 0
                UNION ALL
                SELECT sqlite_master.name as indexname, ii.name as fieldname,
                CASE
                WHEN ti.pk <> 0 AND sqlite_master.name LIKE 'sqlite_autoindex_%' THEN 'PRIMARY'
                WHEN sqlite_master.name LIKE 'sqlite_autoindex_%' THEN 'UNIQUE'
                WHEN sqlite_master.sql LIKE '% UNIQUE %' THEN 'UNIQUE'
                ELSE 'INDEX'
                END as indextype
                FROM sqlite_master
                INNER JOIN pragma_index_xinfo(sqlite_master.name) ii ON ii.name IS NOT NULL
                LEFT JOIN pragma_table_info(" . $this->escape(strtolower($table)) . ") ti ON ti.name = ii.name
                WHERE sqlite_master.type='index' AND sqlite_master.tbl_name = " . $this->escape(strtolower($table)) . ' COLLATE NOCASE';

        if (($query = $this->query($sql)) === false) {
            throw new DatabaseException(lang('Database.failGetIndexData'));
        }
        $query = $query->getResultObject();

        $tempVal = [];

        foreach ($query as $row) {
            if ($row->indextype === 'PRIMARY') {
                $tempVal['PRIMARY']['indextype']               = $row->indextype;
                $tempVal['PRIMARY']['indexname']               = $row->indexname;
                $tempVal['PRIMARY']['fields'][$row->fieldname] = $row->fieldname;
            } else {
                $tempVal[$row->indexname]['indextype']               = $row->indextype;
                $tempVal[$row->indexname]['indexname']               = $row->indexname;
                $tempVal[$row->indexname]['fields'][$row->fieldname] = $row->fieldname;
            }
        }

        $retVal = [];

        foreach ($tempVal as $val) {
            $obj                = new stdClass();
            $obj->name          = $val['indexname'];
            $obj->fields        = array_values($val['fields']);
            $obj->type          = $val['indextype'];
            $retVal[$obj->name] = $obj;
        }

        return $retVal;
    }

    /**
     * Returns an array of objects with Foreign key data
     *
     * @return array<string, stdClass>
     */
    protected function _foreignKeyData(string $table): array
    {
        if ($this->supportsForeignKeys() !== true) {
            return [];
        }

        $query   = $this->query("PRAGMA foreign_key_list({$table})")->getResult();
        $indexes = [];

        foreach ($query as $row) {
            $indexes[$row->id]['constraint_name']       = null;
            $indexes[$row->id]['table_name']            = $table;
            $indexes[$row->id]['foreign_table_name']    = $row->table;
            $indexes[$row->id]['column_name'][]         = $row->from;
            $indexes[$row->id]['foreign_column_name'][] = $row->to;
            $indexes[$row->id]['on_delete']             = $row->on_delete;
            $indexes[$row->id]['on_update']             = $row->on_update;
            $indexes[$row->id]['match']                 = $row->match;
        }

        return $this->foreignKeyDataToObjects($indexes);
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
