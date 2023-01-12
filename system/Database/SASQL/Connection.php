<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\SASQL;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Exception;
use stdClass;

/**
 * Connection for SASQL
 */
class Connection extends BaseConnection
{
    /**
     * Database driver
     *
     * @var string
     */
    public $DBDriver = 'SASQL';

    /**
     * Database name
     *
     * @var string
     */
    public $database;

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
    public $schema = '';

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
     * Class constructor
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        // Default schema should be set as DB username
        $this->schema = $this->username;

        // connID is needed
        if($this->connID == false)
            $this->initialize();
    }

    /**
     * Connect to the database.
     *
     * @return mixed
     *
     * @throws DatabaseException
     */
    public function connect(bool $persistent = false)
    {
        $dsn = 'ServerName=' . $this->engine . ';DatabaseName=' . $this->database . ';RetryConnTO=2;CommLinks=tcpip(Host=' . $this->hostname . ';PORT=' . $this->port . ');UID=' . $this->username . ';ENP=' . $this->password . ';CharSet=utf-8';
        if($persistent == true)
            $this->connID = sasql_pconnect($dsn);
        else
            $this->connID = sasql_connect($dsn);
        var_dump($this->connID);
        if($this->connID != false) {
            // In "core" mode (authenticated database), we need to execute the authentication query (or connection will be stuck after 30s)
            if(isset($this->conAuth) && $this->conAuth != '')
                sasql_query($this->connID, "SET TEMPORARY OPTION CONNECTION_AUTHENTICATION='$this->conAuth'");

            return $this->connID;
        }
        else {
            throw new DatabaseException(implode("\n", sasql_error()));
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
        sasql_close($this->connID);
    }

    /**
     * Platform-dependant string escape
     */
    protected function _escapeString(string $str): string
    {
        return sasql_real_escape_string($this->connID, $str);
    }

    /**
     * Insert ID
     */
    public function insertID(): int
    {
        return sasql_insert_id($this->connID) ?? 0;
    }

    /**
     * Generates the SQL for listing tables in a platform-dependent manner.
     *
     * @param string|null $tableName If $tableName is provided will return only this table if exists.
     */
    protected function _listTables(bool $prefixLimit = false, ?string $tableName = null): string
    {
        $sql = 'select TABLE_NAME as "name"'
            . ' from SYSTABLE '
            . ' left join SYSUSER on SYSTABLE.CREATOR = SYSUSER.USER_ID '
            . ' where TABLE_TYPE = \'BASE\' '
            . " and SYSUSER.USER_NAME = '" . $this->schema . "'    ";

        if ($tableName !== null) {
            return $sql .= ' AND TABLE_NAME LIKE ' . $this->escape($tableName);
        }

        if ($prefixLimit === true && $this->DBPrefix !== '') {
            $sql .= " AND TABLE_NAME LIKE '" . $this->escapeLikeString($this->DBPrefix) . "%' "
                . sprintf($this->likeEscapeStr, $this->likeEscapeChar);
        }

        return $sql;
    }

    /**
     * Generates a platform-specific query string so that the column names can be fetched.
     */
    protected function _listColumns(string $table = ''): string
    {
        return 'select COLUMN_NAME '
            . ' from SYSCOLUMN '
            . ' left join SYSTABLE on SYSCOLUMN.TABLE_ID = SYSTABLE.TABLE_ID '
            . ' left join SYSUSER on SYSTABLE.CREATOR = SYSUSER.USER_ID '
            . ' where TABLE_NAME = ' . $this->escape($this->DBPrefix . $table)
            . ' and SYSUSER.USER_NAME = ' . $this->escape($this->schema);
    }

    /**
     * Returns an array of objects with index data
     *
     * @return stdClass[]
     *
     * @throws DatabaseException
     */
    protected function _indexData(string $table): array
    {
        $sql = 'select SYSIDX.INDEX_NAME as index_name,
                       if SYSIDX.INDEX_ID = 0 then \'PRIMARY\' else if SYSIDXCOL.PRIMARY_COLUMN_ID is null then \'UNIQUE\' else \'INDEX\' end if end if as type,
                       indexType,
                       list(SYSCOLUMN.COLUMN_NAME) as index_keys
                  from SYSIDX
             left join SYSTABLE on SYSTABLE.TABLE_ID = SYSIDX.TABLE_ID
             left join SYSIDXCOL on SYSIDX.TABLE_ID = SYSIDXCOL.TABLE_ID and SYSIDX.INDEX_ID = SYSIDXCOL.INDEX_ID
             left join SYSUSER on SYSTABLE.CREATOR = SYSUSER.USER_ID
             left join SYSCOLUMN on SYSCOLUMN.TABLE_ID = SYSIDXCOL.TABLE_ID and SYSCOLUMN.COLUMN_ID = SYSIDXCOL.COLUMN_ID
             left join dbo.sa_index_levels(' . $this->escape($table) . ', ' . $this->escape($this->schema) . ') on tableId = SYSIDX.TABLE_ID and IndexName = index_name
                 where TABLE_NAME = ' . $this->escape($table) . '
                   and SYSUSER.USER_NAME = ' . $this->escape($this->schema) . '
              group by index_name, type, indexType';

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
            // Could also check indexType
            $obj->type   = $row->type;

            $retVal[$obj->name] = $obj;
        }

        return $retVal;
    }

    /**
     * // @TODO BH
     * Returns an array of objects with Foreign key data
     * referenced_object_id  parent_object_id
     *
     * @return stdClass[]
     *
     * @throws DatabaseException
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
     * // @TODO BH
     * Disables foreign key checks temporarily.
     *
     * @return string
     */
    protected function _disableForeignKeyChecks()
    {
        return 'EXEC sp_MSforeachtable "ALTER TABLE ? NOCHECK CONSTRAINT ALL"';
    }

    /**
     * // @TODO BH
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
     * @return stdClass[]
     *
     * @throws DatabaseException
     */
    protected function _fieldData(string $table): array
    {
        $sql = 'select SYSTABCOL.COLUMN_NAME, SYSDOMAIN.DOMAIN_NAME as DATA_TYPE, SYSTABCOL.WIDTH as CHARACTER_MAXIMUM_LENGTH,
                       SYSTABCOL.SCALE as NUMERIC_PRECISION, SYSTABCOL."DEFAULT" as COLUMN_DEFAULT, SYSTABCOL.NULLS as NULLABLE,
                       if exists (select 1 from sp_pkeys(' . $this->escape(($table)) . ') where COLUMN_NAME = SYSTABCOL.COLUMN_NAME) then 1 else 0 end if as PRIMARY_KEY
                  from SYSTABCOL
             left join SYSTABLE on SYSTABCOL.TABLE_ID = SYSTABLE.TABLE_ID
             left join SYSDOMAIN on SYSDOMAIN.DOMAIN_ID = SYSTABCOL.DOMAIN_ID
                 where SYSTABLE.TABLE_NAME = ' . $this->escape(($table));

        if (($query = $this->query($sql)) === false) {
            throw new DatabaseException(lang('Database.failGetFieldData'));
        }

        $query  = $query->getResultObject();
        $retVal = [];

        for ($i = 0, $c = count($query); $i < $c; $i++) {
            $retVal[$i] = new stdClass();

            $retVal[$i]->name           = $query[$i]->COLUMN_NAME;
            $retVal[$i]->type           = $query[$i]->DATA_TYPE;
            $retVal[$i]->default        = $query[$i]->COLUMN_DEFAULT;
            $retVal[$i]->nullable       = (bool)$query[$i]->NULLABLE;
            $retVal[$i]->primary_key    = $query[$i]->PRIMARY_KEY;

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
        return sasql_set_option($this->connID, 'auto_commit', 0);
    }

    /**
     * Commit Transaction
     */
    protected function _transCommit(): bool
    {
        return sasql_commit($this->connID);
    }

    /**
     * Rollback Transaction
     */
    protected function _transRollback(): bool
    {
        return sasql_rollback($this->connID);
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

        $errorCode = sasql_errorcode($this->connID);
        if($errorCode != 0) {
            $error['code']      = sasql_sqlstate($this->connID) . '/' . $errorCode;
            $error['message']   = sasql_error($this->connID);
        }

        return $error;
    }

    /**
     * Returns the total number of rows affected by this query.
     */
    public function affectedRows(): int
    {
        return sasql_affected_rows($this->connID);
    }

    /**
     * // @TODO BH
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
     * @return false|resource
     */
    protected function execute(string $sql)
    {
        $stmt = sasql_query($this->connID, $sql);

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
     *
     * @deprecated Use `error()` instead.
     */
    public function getError()
    {
        $error = [
            'code'    => '00000',
            'message' => '',
        ];

        $errorCode = sasql_errorcode($this->connID);
        if($errorCode != 0) {
            $error['code']      = sasql_sqlstate($this->connID) . '/' . $errorCode;
            $error['message']   = sasql_error($this->connID);
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

        if (! $this->connID) {
            $this->initialize();
        }

        $sql = 'select top 1 VERSION from SYSHISTORY order by LAST_TIME desc';
        $query = $this->query($sql);
        $query = $query->getRow();

        return isset($query->VERSION) ? $query->VERSION : false;
    }

    /**
     * // @TODO BH
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
