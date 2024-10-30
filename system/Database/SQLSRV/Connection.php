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

namespace CodeIgniter\Database\SQLSRV;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use stdClass;

/**
 * Connection for SQLSRV
 *
 * @extends BaseConnection<resource, resource>
 */
class Connection extends BaseConnection
{
    /**
     * Database driver
     *
     * @var string
     */
    public $DBDriver = 'SQLSRV';

    /**
     * Database name
     *
     * @var string
     */
    public $database;

    /**
     * Scrollable flag
     *
     * Determines what cursor type to use when executing queries.
     *
     * FALSE or SQLSRV_CURSOR_FORWARD would increase performance,
     * but would disable num_rows() (and possibly insert_id())
     *
     * @var false|string
     */
    public $scrollable;

    /**
     * Identifier escape character
     *
     * @var string
     */
    public $escapeChar = '"';

    /**
     * Database schema
     *
     * @var string
     */
    public $schema = 'dbo';

    /**
     * Quoted identifier flag
     *
     * Whether to use SQL-92 standard quoted identifier
     * (double quotes) or brackets for identifier escaping.
     *
     * @var bool
     */
    protected $_quoted_identifier = true;

    /**
     * List of reserved identifiers
     *
     * Identifiers that must NOT be escaped.
     *
     * @var list<string>
     */
    protected $_reserved_identifiers = ['*'];

    /**
     * Class constructor
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        // This is only supported as of SQLSRV 3.0
        if ($this->scrollable === null) {
            $this->scrollable = defined('SQLSRV_CURSOR_CLIENT_BUFFERED') ? SQLSRV_CURSOR_CLIENT_BUFFERED : false;
        }
    }

    /**
     * Connect to the database.
     *
     * @return false|resource
     *
     * @throws DatabaseException
     */
    public function connect(bool $persistent = false)
    {
        $charset = in_array(strtolower($this->charset), ['utf-8', 'utf8'], true) ? 'UTF-8' : SQLSRV_ENC_CHAR;

        $connection = [
            'UID'                  => empty($this->username) ? '' : $this->username,
            'PWD'                  => empty($this->password) ? '' : $this->password,
            'Database'             => $this->database,
            'ConnectionPooling'    => $persistent ? 1 : 0,
            'CharacterSet'         => $charset,
            'Encrypt'              => $this->encrypt === true ? 1 : 0,
            'ReturnDatesAsStrings' => 1,
        ];

        // If the username and password are both empty, assume this is a
        // 'Windows Authentication Mode' connection.
        if (empty($connection['UID']) && empty($connection['PWD'])) {
            unset($connection['UID'], $connection['PWD']);
        }

        if (! str_contains($this->hostname, ',') && $this->port !== '') {
            $this->hostname .= ', ' . $this->port;
        }

        sqlsrv_configure('WarningsReturnAsErrors', 0);
        $this->connID = sqlsrv_connect($this->hostname, $connection);

        if ($this->connID !== false) {
            // Determine how identifiers are escaped
            $query = $this->query('SELECT CASE WHEN (@@OPTIONS | 256) = @@OPTIONS THEN 1 ELSE 0 END AS qi');
            $query = $query->getResultObject();

            $this->_quoted_identifier = empty($query) ? false : (bool) $query[0]->qi;
            $this->escapeChar         = ($this->_quoted_identifier) ? '"' : ['[', ']'];

            return $this->connID;
        }

        throw new DatabaseException($this->getAllErrorMessages());
    }

    /**
     * For exception message
     *
     * @internal
     */
    public function getAllErrorMessages(): string
    {
        $errors = [];

        foreach (sqlsrv_errors() as $error) {
            $errors[] = $error['message']
                . ' SQLSTATE: ' . $error['SQLSTATE'] . ', code: ' . $error['code'];
        }

        return implode("\n", $errors);
    }

    /**
     * Keep or establish the connection if no queries have been sent for
     * a length of time exceeding the server's idle timeout.
     *
     * @return void
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
        sqlsrv_close($this->connID);
    }

    /**
     * Platform-dependant string escape
     */
    protected function _escapeString(string $str): string
    {
        return str_replace("'", "''", remove_invisible_characters($str, false));
    }

    /**
     * Insert ID
     */
    public function insertID(): int
    {
        return (int) ($this->query('SELECT SCOPE_IDENTITY() AS insert_id')->getRow()->insert_id ?? 0);
    }

    /**
     * Generates the SQL for listing tables in a platform-dependent manner.
     *
     * @param string|null $tableName If $tableName is provided will return only this table if exists.
     */
    protected function _listTables(bool $prefixLimit = false, ?string $tableName = null): string
    {
        $sql = 'SELECT [TABLE_NAME] AS "name"'
            . ' FROM [INFORMATION_SCHEMA].[TABLES] '
            . ' WHERE '
            . " [TABLE_SCHEMA] = '" . $this->schema . "'    ";

        if ($tableName !== null) {
            return $sql .= ' AND [TABLE_NAME] LIKE ' . $this->escape($tableName);
        }

        if ($prefixLimit && $this->DBPrefix !== '') {
            $sql .= " AND [TABLE_NAME] LIKE '" . $this->escapeLikeString($this->DBPrefix) . "%' "
                . sprintf($this->likeEscapeStr, $this->likeEscapeChar);
        }

        return $sql;
    }

    /**
     * Generates a platform-specific query string so that the column names can be fetched.
     */
    protected function _listColumns(string $table = ''): string
    {
        return 'SELECT [COLUMN_NAME] '
            . ' FROM [INFORMATION_SCHEMA].[COLUMNS]'
            . ' WHERE  [TABLE_NAME] = ' . $this->escape($this->DBPrefix . $table)
            . ' AND [TABLE_SCHEMA] = ' . $this->escape($this->schema);
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
        $sql = 'EXEC sp_helpindex ' . $this->escape($this->schema . '.' . $table);

        if (($query = $this->query($sql)) === false) {
            throw new DatabaseException(lang('Database.failGetIndexData'));
        }
        $query = $query->getResultObject();

        $retVal = [];

        foreach ($query as $row) {
            $obj       = new stdClass();
            $obj->name = $row->index_name;

            $_fields     = explode(',', trim($row->index_keys));
            $obj->fields = array_map(static fn ($v) => trim($v), $_fields);

            if (str_contains($row->index_description, 'primary key located on')) {
                $obj->type = 'PRIMARY';
            } else {
                $obj->type = (str_contains($row->index_description, 'nonclustered, unique')) ? 'UNIQUE' : 'INDEX';
            }

            $retVal[$obj->name] = $obj;
        }

        return $retVal;
    }

    /**
     * Returns an array of objects with Foreign key data
     * referenced_object_id  parent_object_id
     *
     * @return array<string, stdClass>
     *
     * @throws DatabaseException
     */
    protected function _foreignKeyData(string $table): array
    {
        $sql = 'SELECT
                f.name as constraint_name,
                OBJECT_NAME (f.parent_object_id) as table_name,
                COL_NAME(fc.parent_object_id,fc.parent_column_id) column_name,
                OBJECT_NAME(f.referenced_object_id) foreign_table_name,
                COL_NAME(fc.referenced_object_id,fc.referenced_column_id) foreign_column_name,
                rc.delete_rule,
                rc.update_rule,
                rc.match_option
                FROM
                sys.foreign_keys AS f
                INNER JOIN sys.foreign_key_columns AS fc ON f.OBJECT_ID = fc.constraint_object_id
                INNER JOIN sys.tables t ON t.OBJECT_ID = fc.referenced_object_id
                INNER JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc ON rc.CONSTRAINT_NAME = f.name
                WHERE OBJECT_NAME (f.parent_object_id) = ' . $this->escape($table);

        if (($query = $this->query($sql)) === false) {
            throw new DatabaseException(lang('Database.failGetForeignKeyData'));
        }

        $query   = $query->getResultObject();
        $indexes = [];

        foreach ($query as $row) {
            $indexes[$row->constraint_name]['constraint_name']       = $row->constraint_name;
            $indexes[$row->constraint_name]['table_name']            = $row->table_name;
            $indexes[$row->constraint_name]['column_name'][]         = $row->column_name;
            $indexes[$row->constraint_name]['foreign_table_name']    = $row->foreign_table_name;
            $indexes[$row->constraint_name]['foreign_column_name'][] = $row->foreign_column_name;
            $indexes[$row->constraint_name]['on_delete']             = $row->delete_rule;
            $indexes[$row->constraint_name]['on_update']             = $row->update_rule;
            $indexes[$row->constraint_name]['match']                 = $row->match_option;
        }

        return $this->foreignKeyDataToObjects($indexes);
    }

    /**
     * Disables foreign key checks temporarily.
     *
     * @return string
     */
    protected function _disableForeignKeyChecks()
    {
        return 'EXEC sp_MSforeachtable "ALTER TABLE ? NOCHECK CONSTRAINT ALL"';
    }

    /**
     * Enables foreign key checks temporarily.
     *
     * @return string
     */
    protected function _enableForeignKeyChecks()
    {
        return 'EXEC sp_MSforeachtable "ALTER TABLE ? WITH CHECK CHECK CONSTRAINT ALL"';
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
        $sql = 'SELECT
                COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION,
                COLUMN_DEFAULT, IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME= ' . $this->escape(($table));

        if (($query = $this->query($sql)) === false) {
            throw new DatabaseException(lang('Database.failGetFieldData'));
        }

        $query  = $query->getResultObject();
        $retVal = [];

        for ($i = 0, $c = count($query); $i < $c; $i++) {
            $retVal[$i] = new stdClass();

            $retVal[$i]->name = $query[$i]->COLUMN_NAME;
            $retVal[$i]->type = $query[$i]->DATA_TYPE;

            $retVal[$i]->max_length = $query[$i]->CHARACTER_MAXIMUM_LENGTH > 0
                ? $query[$i]->CHARACTER_MAXIMUM_LENGTH
                : $query[$i]->NUMERIC_PRECISION;

            $retVal[$i]->nullable = $query[$i]->IS_NULLABLE !== 'NO';
            $retVal[$i]->default  = $query[$i]->COLUMN_DEFAULT;
        }

        return $retVal;
    }

    /**
     * Begin Transaction
     */
    protected function _transBegin(): bool
    {
        return sqlsrv_begin_transaction($this->connID);
    }

    /**
     * Commit Transaction
     */
    protected function _transCommit(): bool
    {
        return sqlsrv_commit($this->connID);
    }

    /**
     * Rollback Transaction
     */
    protected function _transRollback(): bool
    {
        return sqlsrv_rollback($this->connID);
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
        $error = [
            'code'    => '00000',
            'message' => '',
        ];

        $sqlsrvErrors = sqlsrv_errors(SQLSRV_ERR_ERRORS);

        if (! is_array($sqlsrvErrors)) {
            return $error;
        }

        $sqlsrvError = array_shift($sqlsrvErrors);
        if (isset($sqlsrvError['SQLSTATE'])) {
            $error['code'] = isset($sqlsrvError['code']) ? $sqlsrvError['SQLSTATE'] . '/' . $sqlsrvError['code'] : $sqlsrvError['SQLSTATE'];
        } elseif (isset($sqlsrvError['code'])) {
            $error['code'] = $sqlsrvError['code'];
        }

        if (isset($sqlsrvError['message'])) {
            $error['message'] = $sqlsrvError['message'];
        }

        return $error;
    }

    /**
     * Returns the total number of rows affected by this query.
     */
    public function affectedRows(): int
    {
        return sqlsrv_rows_affected($this->resultID);
    }

    /**
     * Select a specific database table to use.
     *
     * @return bool
     */
    public function setDatabase(?string $databaseName = null)
    {
        if ($databaseName === null || $databaseName === '') {
            $databaseName = $this->database;
        }

        if (empty($this->connID)) {
            $this->initialize();
        }

        if ($this->execute('USE ' . $this->_escapeString($databaseName))) {
            $this->database  = $databaseName;
            $this->dataCache = [];

            return true;
        }

        return false;
    }

    /**
     * Executes the query against the database.
     *
     * @return false|resource
     */
    protected function execute(string $sql)
    {
        $stmt = ($this->scrollable === false || $this->isWriteType($sql)) ?
            sqlsrv_query($this->connID, $sql) :
            sqlsrv_query($this->connID, $sql, [], ['Scrollable' => $this->scrollable]);

        if ($stmt === false) {
            $error = $this->error();

            log_message('error', $error['message']);

            if ($this->DBDebug) {
                throw new DatabaseException($error['message']);
            }
        }

        return $stmt;
    }

    /**
     * Returns the last error encountered by this connection.
     *
     * @return array<string, int|string>
     *
     * @deprecated Use `error()` instead.
     */
    public function getError()
    {
        $error = [
            'code'    => '00000',
            'message' => '',
        ];

        $sqlsrvErrors = sqlsrv_errors(SQLSRV_ERR_ERRORS);

        if (! is_array($sqlsrvErrors)) {
            return $error;
        }

        $sqlsrvError = array_shift($sqlsrvErrors);
        if (isset($sqlsrvError['SQLSTATE'])) {
            $error['code'] = isset($sqlsrvError['code']) ? $sqlsrvError['SQLSTATE'] . '/' . $sqlsrvError['code'] : $sqlsrvError['SQLSTATE'];
        } elseif (isset($sqlsrvError['code'])) {
            $error['code'] = $sqlsrvError['code'];
        }

        if (isset($sqlsrvError['message'])) {
            $error['message'] = $sqlsrvError['message'];
        }

        return $error;
    }

    /**
     * The name of the platform in use (MySQLi, mssql, etc)
     */
    public function getPlatform(): string
    {
        return $this->DBDriver;
    }

    /**
     * Returns a string containing the version of the database being used.
     */
    public function getVersion(): string
    {
        $info = [];
        if (isset($this->dataCache['version'])) {
            return $this->dataCache['version'];
        }

        if (! $this->connID || ($info = sqlsrv_server_info($this->connID)) === []) {
            $this->initialize();
        }

        return isset($info['SQLServerVersion']) ? $this->dataCache['version'] = $info['SQLServerVersion'] : false;
    }

    /**
     * Determines if a query is a "write" type.
     *
     * Overrides BaseConnection::isWriteType, adding additional read query types.
     *
     * @param string $sql
     */
    public function isWriteType($sql): bool
    {
        if (preg_match('/^\s*"?(EXEC\s*sp_rename)\s/i', $sql)) {
            return true;
        }

        return parent::isWriteType($sql);
    }
}
