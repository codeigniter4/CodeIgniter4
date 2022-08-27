<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Postgre;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use ErrorException;
use stdClass;

/**
 * Connection for Postgre
 */
class Connection extends BaseConnection
{
    /**
     * Database driver
     *
     * @var string
     */
    public $DBDriver = 'Postgre';

    /**
     * Database schema
     *
     * @var string
     */
    public $schema = 'public';

    /**
     * Identifier escape character
     *
     * @var string
     */
    public $escapeChar = '"';

    protected $connect_timeout;
    protected $options;
    protected $sslmode;
    protected $service;

    /**
     * Connect to the database.
     *
     * @return mixed
     */
    public function connect(bool $persistent = false)
    {
        if (empty($this->DSN)) {
            $this->buildDSN();
        }

        // Strip pgsql if exists
        if (mb_strpos($this->DSN, 'pgsql:') === 0) {
            $this->DSN = mb_substr($this->DSN, 6);
        }

        // Convert semicolons to spaces.
        $this->DSN = str_replace(';', ' ', $this->DSN);

        $this->connID = $persistent === true ? pg_pconnect($this->DSN) : pg_connect($this->DSN);

        if ($this->connID !== false) {
            if ($persistent === true && pg_connection_status($this->connID) === PGSQL_CONNECTION_BAD && pg_ping($this->connID) === false
            ) {
                return false;
            }

            if (! empty($this->schema)) {
                $this->simpleQuery("SET search_path TO {$this->schema},public");
            }

            if ($this->setClientEncoding($this->charset) === false) {
                return false;
            }
        }

        return $this->connID;
    }

    /**
     * Keep or establish the connection if no queries have been sent for
     * a length of time exceeding the server's idle timeout.
     */
    public function reconnect()
    {
        if (pg_ping($this->connID) === false) {
            $this->connID = false;
        }
    }

    /**
     * Close the database connection.
     */
    protected function _close()
    {
        pg_close($this->connID);
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

        if (! $this->connID || ($pgVersion = pg_version($this->connID)) === false) {
            $this->initialize();
        }

        return isset($pgVersion['server']) ? $this->dataCache['version'] = $pgVersion['server'] : false;
    }

    /**
     * Executes the query against the database.
     *
     * @return false|resource
     */
    protected function execute(string $sql)
    {
        try {
            return pg_query($this->connID, $sql);
        } catch (ErrorException $e) {
            log_message('error', (string) $e);
            if ($this->DBDebug) {
                throw $e;
            }
        }

        return false;
    }

    /**
     * Get the prefix of the function to access the DB.
     */
    protected function getDriverFunctionPrefix(): string
    {
        return 'pg_';
    }

    /**
     * Returns the total number of rows affected by this query.
     */
    public function affectedRows(): int
    {
        return pg_affected_rows($this->resultID);
    }

    /**
     * "Smart" Escape String
     *
     * Escapes data based on type
     *
     * @param mixed $str
     *
     * @return mixed
     */
    public function escape($str)
    {
        if (! $this->connID) {
            $this->initialize();
        }

        if (is_string($str) || (is_object($str) && method_exists($str, '__toString'))) {
            return pg_escape_literal($this->connID, $str);
        }

        if (is_bool($str)) {
            return $str ? 'TRUE' : 'FALSE';
        }

        return parent::escape($str);
    }

    /**
     * Platform-dependant string escape
     */
    protected function _escapeString(string $str): string
    {
        if (! $this->connID) {
            $this->initialize();
        }

        return pg_escape_string($this->connID, $str);
    }

    /**
     * Generates the SQL for listing tables in a platform-dependent manner.
     *
     * @param string|null $tableName If $tableName is provided will return only this table if exists.
     */
    protected function _listTables(bool $prefixLimit = false, ?string $tableName = null): string
    {
        $sql = 'SELECT "table_name" FROM "information_schema"."tables" WHERE "table_schema" = \'' . $this->schema . "'";

        if ($tableName !== null) {
            return $sql . ' AND "table_name" LIKE ' . $this->escape($tableName);
        }

        if ($prefixLimit !== false && $this->DBPrefix !== '') {
            return $sql . ' AND "table_name" LIKE \''
                . $this->escapeLikeString($this->DBPrefix) . "%' "
                . sprintf($this->likeEscapeStr, $this->likeEscapeChar);
        }

        return $sql;
    }

    /**
     * Generates a platform-specific query string so that the column names can be fetched.
     */
    protected function _listColumns(string $table = ''): string
    {
        return 'SELECT "column_name"
			FROM "information_schema"."columns"
			WHERE LOWER("table_name") = '
                . $this->escape($this->DBPrefix . strtolower($table))
                . ' ORDER BY "ordinal_position"';
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
        $sql = 'SELECT "column_name", "data_type", "character_maximum_length", "numeric_precision", "column_default",  "is_nullable"
            FROM "information_schema"."columns"
            WHERE LOWER("table_name") = '
                . $this->escape(strtolower($table))
                . ' ORDER BY "ordinal_position"';

        if (($query = $this->query($sql)) === false) {
            throw new DatabaseException(lang('Database.failGetFieldData'));
        }
        $query = $query->getResultObject();

        $retVal = [];

        for ($i = 0, $c = count($query); $i < $c; $i++) {
            $retVal[$i] = new stdClass();

            $retVal[$i]->name       = $query[$i]->column_name;
            $retVal[$i]->type       = $query[$i]->data_type;
            $retVal[$i]->nullable   = $query[$i]->is_nullable === 'YES';
            $retVal[$i]->default    = $query[$i]->column_default;
            $retVal[$i]->max_length = $query[$i]->character_maximum_length > 0 ? $query[$i]->character_maximum_length : $query[$i]->numeric_precision;
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
        $sql = 'SELECT "indexname", "indexdef"
			FROM "pg_indexes"
			WHERE LOWER("tablename") = ' . $this->escape(strtolower($table)) . '
			AND "schemaname" = ' . $this->escape('public');

        if (($query = $this->query($sql)) === false) {
            throw new DatabaseException(lang('Database.failGetIndexData'));
        }
        $query = $query->getResultObject();

        $retVal = [];

        foreach ($query as $row) {
            $obj         = new stdClass();
            $obj->name   = $row->indexname;
            $_fields     = explode(',', preg_replace('/^.*\((.+?)\)$/', '$1', trim($row->indexdef)));
            $obj->fields = array_map(static fn ($v) => trim($v), $_fields);

            if (strpos($row->indexdef, 'CREATE UNIQUE INDEX pk') === 0) {
                $obj->type = 'PRIMARY';
            } else {
                $obj->type = (strpos($row->indexdef, 'CREATE UNIQUE') === 0) ? 'UNIQUE' : 'INDEX';
            }

            $retVal[$obj->name] = $obj;
        }

        return $retVal;
    }

    /**
     * Returns an array of objects with Foreign key data
     *
     * @throws DatabaseException
     *
     * @return stdClass[]
     */
    protected function _foreignKeyData(string $table): array
    {
        $sql = 'SELECT
            tc.constraint_name, tc.table_name, kcu.column_name,
            ccu.table_name AS foreign_table_name,
            ccu.column_name AS foreign_column_name
        FROM information_schema.table_constraints AS tc
        JOIN information_schema.key_column_usage AS kcu
            ON tc.constraint_name = kcu.constraint_name
        JOIN information_schema.constraint_column_usage AS ccu
            ON ccu.constraint_name = tc.constraint_name
        WHERE constraint_type = ' . $this->escape('FOREIGN KEY') . ' AND
            tc.table_name = ' . $this->escape($table);

        if (($query = $this->query($sql)) === false) {
            throw new DatabaseException(lang('Database.failGetForeignKeyData'));
        }

        $query  = $query->getResultObject();
        $retVal = [];

        foreach ($query as $row) {
            $obj = new stdClass();

            $obj->constraint_name     = $row->constraint_name;
            $obj->table_name          = $row->table_name;
            $obj->column_name         = $row->column_name;
            $obj->foreign_table_name  = $row->foreign_table_name;
            $obj->foreign_column_name = $row->foreign_column_name;

            $retVal[] = $obj;
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
        return 'SET CONSTRAINTS ALL DEFERRED';
    }

    /**
     * Returns platform-specific SQL to enable foreign key checks.
     *
     * @return string
     */
    protected function _enableForeignKeyChecks()
    {
        return 'SET CONSTRAINTS ALL IMMEDIATE;';
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
            'code'    => '',
            'message' => pg_last_error($this->connID) ?: '',
        ];
    }

    /**
     * @return int|string
     */
    public function insertID()
    {
        $v = pg_version($this->connID);
        // 'server' key is only available since PostgreSQL 7.4
        $v = explode(' ', $v['server'])[0] ?? 0;

        $table  = func_num_args() > 0 ? func_get_arg(0) : null;
        $column = func_num_args() > 1 ? func_get_arg(1) : null;

        if ($table === null && $v >= '8.1') {
            $sql = 'SELECT LASTVAL() AS ins_id';
        } elseif ($table !== null) {
            if ($column !== null && $v >= '8.0') {
                $sql   = "SELECT pg_get_serial_sequence('{$table}', '{$column}') AS seq";
                $query = $this->query($sql);
                $query = $query->getRow();
                $seq   = $query->seq;
            } else {
                // seq_name passed in table parameter
                $seq = $table;
            }

            $sql = "SELECT CURRVAL('{$seq}') AS ins_id";
        } else {
            return pg_last_oid($this->resultID);
        }

        $query = $this->query($sql);
        $query = $query->getRow();

        return (int) $query->ins_id;
    }

    /**
     * Build a DSN from the provided parameters
     */
    protected function buildDSN()
    {
        if ($this->DSN !== '') {
            $this->DSN = '';
        }

        // If UNIX sockets are used, we shouldn't set a port
        if (strpos($this->hostname, '/') !== false) {
            $this->port = '';
        }

        if ($this->hostname !== '') {
            $this->DSN = "host={$this->hostname} ";
        }

        // ctype_digit only accepts strings
        $port = (string) $this->port;

        if ($port !== '' && ctype_digit($port)) {
            $this->DSN .= "port={$port} ";
        }

        if ($this->username !== '') {
            $this->DSN .= "user={$this->username} ";

            // An empty password is valid!
            // password must be set to null to ignore it.
            if ($this->password !== null) {
                $this->DSN .= "password='{$this->password}' ";
            }
        }

        if ($this->database !== '') {
            $this->DSN .= "dbname={$this->database} ";
        }

        // We don't have these options as elements in our standard configuration
        // array, but they might be set by parse_url() if the configuration was
        // provided via string> Example:
        //
        // Postgre://username:password@localhost:5432/database?connect_timeout=5&sslmode=1
        foreach (['connect_timeout', 'options', 'sslmode', 'service'] as $key) {
            if (isset($this->{$key}) && is_string($this->{$key}) && $this->{$key} !== '') {
                $this->DSN .= "{$key}='{$this->{$key}}' ";
            }
        }

        $this->DSN = rtrim($this->DSN);
    }

    /**
     * Set client encoding
     */
    protected function setClientEncoding(string $charset): bool
    {
        return pg_set_client_encoding($this->connID, $charset) === 0;
    }

    /**
     * Begin Transaction
     */
    protected function _transBegin(): bool
    {
        return (bool) pg_query($this->connID, 'BEGIN');
    }

    /**
     * Commit Transaction
     */
    protected function _transCommit(): bool
    {
        return (bool) pg_query($this->connID, 'COMMIT');
    }

    /**
     * Rollback Transaction
     */
    protected function _transRollback(): bool
    {
        return (bool) pg_query($this->connID, 'ROLLBACK');
    }

    /**
     * Determines if a query is a "write" type.
     *
     * Overrides BaseConnection::isWriteType, adding additional read query types.
     *
     * @param mixed $sql
     */
    public function isWriteType($sql): bool
    {
        if (preg_match('#^(INSERT|UPDATE).*RETURNING\s.+(\,\s?.+)*$#is', $sql)) {
            return false;
        }

        return parent::isWriteType($sql);
    }
}
