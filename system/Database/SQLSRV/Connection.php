<?php

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
use Exception;
use stdClass;

/**
 * Connection for SQLSRV
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
     * @var mixed
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
     * @var string[]
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
     * @throws DatabaseException
     *
     * @return mixed
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

        $errors = [];

        foreach (sqlsrv_errors(SQLSRV_ERR_ERRORS) as $error) {
            $errors[] = preg_replace('/(\[.+\]\[.+\](?:\[.+\])?)(.+)/', '$2', $error['message']);
        }

        throw new DatabaseException(implode("\n", $errors));
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
        return $this->query('SELECT SCOPE_IDENTITY() AS insert_id')->getRow()->insert_id ?? 0;
    }

    /**
     * Generates the SQL for listing tables in a platform-dependent manner.
     */
    protected function _listTables(bool $prefixLimit = false): string
    {
        $sql = 'SELECT [TABLE_NAME] AS "name"'
            . ' FROM [INFORMATION_SCHEMA].[TABLES] '
            . ' WHERE '
            . " [TABLE_SCHEMA] = '" . $this->schema . "'    ";

        if ($prefixLimit === true && $this->DBPrefix !== '') {
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
     * @throws DatabaseException
     *
     * @return stdClass[]
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
            $obj->fields = array_map(static function ($v) {
                return trim($v);
            }, $_fields);

            if (strpos($row->index_description, 'primary key located on') !== false) {
                $obj->type = 'PRIMARY';
            } else {
                $obj->type = (strpos($row->index_description, 'nonclustered, unique') !== false) ? 'UNIQUE' : 'INDEX';
            }

            $retVal[$obj->name] = $obj;
        }

        return $retVal;
    }

    /**
     * Returns an array of objects with Foreign key data
     * referenced_object_id  parent_object_id
     *
     * @throws DatabaseException
     *
     * @return stdClass[]
     */
    protected function _foreignKeyData(string $table): array
    {
        $sql = 'SELECT '
            . 'f.name as constraint_name, '
            . 'OBJECT_NAME (f.parent_object_id) as table_name, '
            . 'COL_NAME(fc.parent_object_id,fc.parent_column_id) column_name, '
            . 'OBJECT_NAME(f.referenced_object_id) foreign_table_name, '
            . 'COL_NAME(fc.referenced_object_id,fc.referenced_column_id) foreign_column_name '
            . 'FROM  '
            . 'sys.foreign_keys AS f '
            . 'INNER JOIN  '
            . 'sys.foreign_key_columns AS fc  '
            . 'ON f.OBJECT_ID = fc.constraint_object_id '
            . 'INNER JOIN  '
            . 'sys.tables t  '
            . 'ON t.OBJECT_ID = fc.referenced_object_id '
            . 'WHERE  '
            . 'OBJECT_NAME (f.parent_object_id) = ' . $this->escape($table);

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
     * @throws DatabaseException
     *
     * @return stdClass[]
     */
    protected function _fieldData(string $table): array
    {
        $sql = 'SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, COLUMN_DEFAULT
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_NAME= ' . $this->escape(($table));

        if (($query = $this->query($sql)) === false) {
            throw new DatabaseException(lang('Database.failGetFieldData'));
        }

        $query  = $query->getResultObject();
        $retVal = [];

        for ($i = 0, $c = count($query); $i < $c; $i++) {
            $retVal[$i] = new stdClass();

            $retVal[$i]->name    = $query[$i]->COLUMN_NAME;
            $retVal[$i]->type    = $query[$i]->DATA_TYPE;
            $retVal[$i]->default = $query[$i]->COLUMN_DEFAULT;

            $retVal[$i]->max_length = $query[$i]->CHARACTER_MAXIMUM_LENGTH > 0
                ? $query[$i]->CHARACTER_MAXIMUM_LENGTH
                : $query[$i]->NUMERIC_PRECISION;
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
     * @return mixed
     */
    public function setDatabase(?string $databaseName = null)
    {
        if (empty($databaseName)) {
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
     * @return mixed
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
                throw new Exception($error['message']);
            }
        }

        return $stmt;
    }

    /**
     * Returns the last error encountered by this connection.
     *
     * @return mixed
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
        if (isset($this->dataCache['version'])) {
            return $this->dataCache['version'];
        }

        if (! $this->connID || empty($info = sqlsrv_server_info($this->connID))) {
            $this->initialize();
        }

        return isset($info['SQLServerVersion']) ? $this->dataCache['version'] = $info['SQLServerVersion'] : false;
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
        if (preg_match('/^\s*"?(EXEC\s*sp_rename)\s/i', $sql)) {
            return true;
        }

        return parent::isWriteType($sql);
    }
}
