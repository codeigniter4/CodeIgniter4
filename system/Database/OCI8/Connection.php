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

namespace CodeIgniter\Database\OCI8;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Query;
use ErrorException;
use stdClass;

/**
 * Connection for OCI8
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
    protected $DBDriver = 'OCI8';

    /**
     * Identifier escape character
     *
     * @var string
     */
    public $escapeChar = '"';

    /**
     * List of reserved identifiers
     *
     * Identifiers that must NOT be escaped.
     *
     * @var array
     */
    protected $reservedIdentifiers = [
        '*',
        'rownum',
    ];

    protected $validDSNs = [
        // TNS
        'tns' => '/^\(DESCRIPTION=(\(.+\)){2,}\)$/',
        // Easy Connect string (Oracle 10g+).
        // https://docs.oracle.com/en/database/oracle/oracle-database/23/netag/configuring-naming-methods.html#GUID-36F3A17D-843C-490A-8A23-FB0FE005F8E8
        // [//]host[:port][/[service_name][:server_type][/instance_name]]
        'ec' => '/^
            (\/\/)?
            (\[)?[a-z0-9.:_-]+(\])? # Host or IP address
            (:[1-9][0-9]{0,4})?     # Port
            (
                (\/)
                ([a-z0-9.$_]+)?     # Service name
                (:[a-z]+)?          # Server type
                (\/[a-z0-9$_]+)?    # Instance name
            )?
        $/ix',
        // Instance name (defined in tnsnames.ora)
        'in' => '/^[a-z0-9$_]+$/i',
    ];

    /**
     * Reset $stmtId flag
     *
     * Used by storedProcedure() to prevent execute() from
     * re-setting the statement ID.
     */
    protected $resetStmtId = true;

    /**
     * Statement ID
     *
     * @var resource
     */
    protected $stmtId;

    /**
     * Commit mode flag
     *
     * @used-by PreparedQuery::_execute()
     *
     * @var int
     */
    public $commitMode = OCI_COMMIT_ON_SUCCESS;

    /**
     * Cursor ID
     *
     * @var resource
     */
    protected $cursorId;

    /**
     * Latest inserted table name.
     *
     * @used-by PreparedQuery::_execute()
     *
     * @var string|null
     */
    public $lastInsertedTableName;

    /**
     * confirm DSN format.
     */
    private function isValidDSN(): bool
    {
        if ($this->DSN === null || $this->DSN === '') {
            return false;
        }

        foreach ($this->validDSNs as $regexp) {
            if (preg_match($regexp, $this->DSN)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Connect to the database.
     *
     * @return false|resource
     */
    public function connect(bool $persistent = false)
    {
        if (! $this->isValidDSN()) {
            $this->buildDSN();
        }

        $func = $persistent ? 'oci_pconnect' : 'oci_connect';

        return ($this->charset === '')
            ? $func($this->username, $this->password, $this->DSN)
            : $func($this->username, $this->password, $this->DSN, $this->charset);
    }

    /**
     * Keep or establish the connection if no queries have been sent for
     * a length of time exceeding the server's idle timeout.
     *
     * @return void
     */
    public function reconnect()
    {
    }

    /**
     * Close the database connection.
     *
     * @return void
     */
    protected function _close()
    {
        if (is_resource($this->cursorId)) {
            oci_free_statement($this->cursorId);
        }
        if (is_resource($this->stmtId)) {
            oci_free_statement($this->stmtId);
        }
        oci_close($this->connID);
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

        if (! $this->connID || ($versionString = oci_server_version($this->connID)) === false) {
            return '';
        }
        if (preg_match('#Release\s(\d+(?:\.\d+)+)#', $versionString, $match)) {
            return $this->dataCache['version'] = $match[1];
        }

        return '';
    }

    /**
     * Executes the query against the database.
     *
     * @return false|resource
     */
    protected function execute(string $sql)
    {
        try {
            if ($this->resetStmtId === true) {
                $this->stmtId = oci_parse($this->connID, $sql);
            }

            oci_set_prefetch($this->stmtId, 1000);

            $result          = oci_execute($this->stmtId, $this->commitMode) ? $this->stmtId : false;
            $insertTableName = $this->parseInsertTableName($sql);

            if ($result && $insertTableName !== '') {
                $this->lastInsertedTableName = $insertTableName;
            }

            return $result;
        } catch (ErrorException $e) {
            log_message('error', (string) $e);

            if ($this->DBDebug) {
                throw new DatabaseException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return false;
    }

    /**
     * Get the table name for the insert statement from sql.
     */
    public function parseInsertTableName(string $sql): string
    {
        $commentStrippedSql = preg_replace(['/\/\*(.|\n)*?\*\//m', '/--.+/'], '', $sql);
        $isInsertQuery      = str_starts_with(strtoupper(ltrim($commentStrippedSql)), 'INSERT');

        if (! $isInsertQuery) {
            return '';
        }

        preg_match('/(?is)\b(?:into)\s+("?\w+"?)/', $commentStrippedSql, $match);
        $tableName = $match[1] ?? '';

        return str_starts_with($tableName, '"') ? trim($tableName, '"') : strtoupper($tableName);
    }

    /**
     * Returns the total number of rows affected by this query.
     */
    public function affectedRows(): int
    {
        return oci_num_rows($this->stmtId);
    }

    /**
     * Generates the SQL for listing tables in a platform-dependent manner.
     *
     * @param string|null $tableName If $tableName is provided will return only this table if exists.
     */
    protected function _listTables(bool $prefixLimit = false, ?string $tableName = null): string
    {
        $sql = 'SELECT "TABLE_NAME" FROM "USER_TABLES"';

        if ($tableName !== null) {
            return $sql . ' WHERE "TABLE_NAME" LIKE ' . $this->escape($tableName);
        }

        if ($prefixLimit && $this->DBPrefix !== '') {
            return $sql . ' WHERE "TABLE_NAME" LIKE \'' . $this->escapeLikeString($this->DBPrefix) . "%' "
                    . sprintf($this->likeEscapeStr, $this->likeEscapeChar);
        }

        return $sql;
    }

    /**
     * Generates a platform-specific query string so that the column names can be fetched.
     */
    protected function _listColumns(string $table = ''): string
    {
        if (str_contains($table, '.')) {
            sscanf($table, '%[^.].%s', $owner, $table);
        } else {
            $owner = $this->username;
        }

        return 'SELECT COLUMN_NAME FROM ALL_TAB_COLUMNS
			WHERE UPPER(OWNER) = ' . $this->escape(strtoupper($owner)) . '
				AND UPPER(TABLE_NAME) = ' . $this->escape(strtoupper($this->DBPrefix . $table));
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
        if (str_contains($table, '.')) {
            sscanf($table, '%[^.].%s', $owner, $table);
        } else {
            $owner = $this->username;
        }

        $sql = 'SELECT COLUMN_NAME, DATA_TYPE, CHAR_LENGTH, DATA_PRECISION, DATA_LENGTH, DATA_DEFAULT, NULLABLE
			FROM ALL_TAB_COLUMNS
			WHERE UPPER(OWNER) = ' . $this->escape(strtoupper($owner)) . '
				AND UPPER(TABLE_NAME) = ' . $this->escape(strtoupper($table));

        if (($query = $this->query($sql)) === false) {
            throw new DatabaseException(lang('Database.failGetFieldData'));
        }
        $query = $query->getResultObject();

        $retval = [];

        for ($i = 0, $c = count($query); $i < $c; $i++) {
            $retval[$i]       = new stdClass();
            $retval[$i]->name = $query[$i]->COLUMN_NAME;
            $retval[$i]->type = $query[$i]->DATA_TYPE;

            $length = $query[$i]->CHAR_LENGTH > 0 ? $query[$i]->CHAR_LENGTH : $query[$i]->DATA_PRECISION;
            $length ??= $query[$i]->DATA_LENGTH;

            $retval[$i]->max_length = $length;

            $retval[$i]->nullable = $query[$i]->NULLABLE === 'Y';
            $retval[$i]->default  = $query[$i]->DATA_DEFAULT;
        }

        return $retval;
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
        if (str_contains($table, '.')) {
            sscanf($table, '%[^.].%s', $owner, $table);
        } else {
            $owner = $this->username;
        }

        $sql = 'SELECT AIC.INDEX_NAME, UC.CONSTRAINT_TYPE, AIC.COLUMN_NAME '
            . ' FROM ALL_IND_COLUMNS AIC '
            . ' LEFT JOIN USER_CONSTRAINTS UC ON AIC.INDEX_NAME = UC.CONSTRAINT_NAME AND AIC.TABLE_NAME = UC.TABLE_NAME '
            . 'WHERE AIC.TABLE_NAME = ' . $this->escape(strtolower($table)) . ' '
            . 'AND AIC.TABLE_OWNER = ' . $this->escape(strtoupper($owner)) . ' '
            . ' ORDER BY UC.CONSTRAINT_TYPE, AIC.COLUMN_POSITION';

        if (($query = $this->query($sql)) === false) {
            throw new DatabaseException(lang('Database.failGetIndexData'));
        }
        $query = $query->getResultObject();

        $retVal          = [];
        $constraintTypes = [
            'P' => 'PRIMARY',
            'U' => 'UNIQUE',
        ];

        foreach ($query as $row) {
            if (isset($retVal[$row->INDEX_NAME])) {
                $retVal[$row->INDEX_NAME]->fields[] = $row->COLUMN_NAME;

                continue;
            }

            $retVal[$row->INDEX_NAME]         = new stdClass();
            $retVal[$row->INDEX_NAME]->name   = $row->INDEX_NAME;
            $retVal[$row->INDEX_NAME]->fields = [$row->COLUMN_NAME];
            $retVal[$row->INDEX_NAME]->type   = $constraintTypes[$row->CONSTRAINT_TYPE] ?? 'INDEX';
        }

        return $retVal;
    }

    /**
     * Returns an array of objects with Foreign key data
     *
     * @return array<string, stdClass>
     *
     * @throws DatabaseException
     */
    protected function _foreignKeyData(string $table): array
    {
        $sql = 'SELECT
                acc.constraint_name,
                acc.table_name,
                acc.column_name,
                ccu.table_name foreign_table_name,
                accu.column_name foreign_column_name,
                ac.delete_rule
                FROM all_cons_columns acc
                JOIN all_constraints ac ON acc.owner = ac.owner
                AND acc.constraint_name = ac.constraint_name
                JOIN all_constraints ccu ON ac.r_owner = ccu.owner
                AND ac.r_constraint_name = ccu.constraint_name
                JOIN all_cons_columns accu ON accu.constraint_name = ccu.constraint_name
                AND accu.position = acc.position
                AND accu.table_name = ccu.table_name
                WHERE ac.constraint_type = ' . $this->escape('R') . '
                AND acc.table_name = ' . $this->escape($table);

        $query = $this->query($sql);

        if ($query === false) {
            throw new DatabaseException(lang('Database.failGetForeignKeyData'));
        }

        $query   = $query->getResultObject();
        $indexes = [];

        foreach ($query as $row) {
            $indexes[$row->CONSTRAINT_NAME]['constraint_name']       = $row->CONSTRAINT_NAME;
            $indexes[$row->CONSTRAINT_NAME]['table_name']            = $row->TABLE_NAME;
            $indexes[$row->CONSTRAINT_NAME]['column_name'][]         = $row->COLUMN_NAME;
            $indexes[$row->CONSTRAINT_NAME]['foreign_table_name']    = $row->FOREIGN_TABLE_NAME;
            $indexes[$row->CONSTRAINT_NAME]['foreign_column_name'][] = $row->FOREIGN_COLUMN_NAME;
            $indexes[$row->CONSTRAINT_NAME]['on_delete']             = $row->DELETE_RULE;
            $indexes[$row->CONSTRAINT_NAME]['on_update']             = null;
            $indexes[$row->CONSTRAINT_NAME]['match']                 = null;
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
        return <<<'SQL'
            BEGIN
              FOR c IN
              (SELECT c.owner, c.table_name, c.constraint_name
               FROM user_constraints c, user_tables t
               WHERE c.table_name = t.table_name
               AND c.status = 'ENABLED'
               AND c.constraint_type = 'R'
               AND t.iot_type IS NULL
               ORDER BY c.constraint_type DESC)
              LOOP
                dbms_utility.exec_ddl_statement('alter table "' || c.owner || '"."' || c.table_name || '" disable constraint "' || c.constraint_name || '"');
              END LOOP;
            END;
            SQL;
    }

    /**
     * Returns platform-specific SQL to enable foreign key checks.
     *
     * @return string
     */
    protected function _enableForeignKeyChecks()
    {
        return <<<'SQL'
            BEGIN
              FOR c IN
              (SELECT c.owner, c.table_name, c.constraint_name
               FROM user_constraints c, user_tables t
               WHERE c.table_name = t.table_name
               AND c.status = 'DISABLED'
               AND c.constraint_type = 'R'
               AND t.iot_type IS NULL
               ORDER BY c.constraint_type DESC)
              LOOP
                dbms_utility.exec_ddl_statement('alter table "' || c.owner || '"."' || c.table_name || '" enable constraint "' || c.constraint_name || '"');
              END LOOP;
            END;
            SQL;
    }

    /**
     * Get cursor. Returns a cursor from the database
     *
     * @return resource
     */
    public function getCursor()
    {
        return $this->cursorId = oci_new_cursor($this->connID);
    }

    /**
     * Executes a stored procedure
     *
     * @param string $procedureName procedure name to execute
     * @param array  $params        params array keys
     *                              KEY      OPTIONAL  NOTES
     *                              name     no        the name of the parameter should be in :<param_name> format
     *                              value    no        the value of the parameter.  If this is an OUT or IN OUT parameter,
     *                              this should be a reference to a variable
     *                              type     yes       the type of the parameter
     *                              length   yes       the max size of the parameter
     *
     * @return bool|Query|Result
     */
    public function storedProcedure(string $procedureName, array $params)
    {
        if ($procedureName === '') {
            throw new DatabaseException(lang('Database.invalidArgument', [$procedureName]));
        }

        // Build the query string
        $sql = sprintf(
            'BEGIN %s (' . substr(str_repeat(',%s', count($params)), 1) . '); END;',
            $procedureName,
            ...array_map(static fn ($row) => $row['name'], $params)
        );

        $this->resetStmtId = false;
        $this->stmtId      = oci_parse($this->connID, $sql);
        $this->bindParams($params);
        $result            = $this->query($sql);
        $this->resetStmtId = true;

        return $result;
    }

    /**
     * Bind parameters
     *
     * @param array $params
     *
     * @return void
     */
    protected function bindParams($params)
    {
        if (! is_array($params) || ! is_resource($this->stmtId)) {
            return;
        }

        foreach ($params as $param) {
            oci_bind_by_name(
                $this->stmtId,
                $param['name'],
                $param['value'],
                $param['length'] ?? -1,
                $param['type'] ?? SQLT_CHR
            );
        }
    }

    /**
     * Returns the last error code and message.
     *
     * Must return an array with keys 'code' and 'message':
     *
     *  return ['code' => null, 'message' => null);
     */
    public function error(): array
    {
        // oci_error() returns an array that already contains
        // 'code' and 'message' keys, but it can return false
        // if there was no error ....
        $error     = oci_error();
        $resources = [$this->cursorId, $this->stmtId, $this->connID];

        foreach ($resources as $resource) {
            if (is_resource($resource)) {
                $error = oci_error($resource);
                break;
            }
        }

        return is_array($error)
            ? $error
            : [
                'code'    => '',
                'message' => '',
            ];
    }

    public function insertID(): int
    {
        if (empty($this->lastInsertedTableName)) {
            return 0;
        }

        $indexs     = $this->getIndexData($this->lastInsertedTableName);
        $fieldDatas = $this->getFieldData($this->lastInsertedTableName);

        if ($indexs === [] || $fieldDatas === []) {
            return 0;
        }

        $columnTypeList    = array_column($fieldDatas, 'type', 'name');
        $primaryColumnName = '';

        foreach ($indexs as $index) {
            if ($index->type !== 'PRIMARY' || count($index->fields) !== 1) {
                continue;
            }

            $primaryColumnName = $this->protectIdentifiers($index->fields[0], false, false);
            $primaryColumnType = $columnTypeList[$primaryColumnName];

            if ($primaryColumnType !== 'NUMBER') {
                $primaryColumnName = '';
            }
        }

        if ($primaryColumnName === '') {
            return 0;
        }

        $query           = $this->query('SELECT DATA_DEFAULT FROM USER_TAB_COLUMNS WHERE TABLE_NAME = ? AND COLUMN_NAME = ?', [$this->lastInsertedTableName, $primaryColumnName])->getRow();
        $lastInsertValue = str_replace('nextval', 'currval', $query->DATA_DEFAULT ?? '0');
        $query           = $this->query(sprintf('SELECT %s SEQ FROM DUAL', $lastInsertValue))->getRow();

        return (int) ($query->SEQ ?? 0);
    }

    /**
     * Build a DSN from the provided parameters
     *
     * @return void
     */
    protected function buildDSN()
    {
        if ($this->DSN !== '') {
            $this->DSN = '';
        }

        // Legacy support for TNS in the hostname configuration field
        $this->hostname = str_replace(["\n", "\r", "\t", ' '], '', $this->hostname);

        if (preg_match($this->validDSNs['tns'], $this->hostname)) {
            $this->DSN = $this->hostname;

            return;
        }

        $isEasyConnectableHostName = $this->hostname !== '' && ! str_contains($this->hostname, '/') && ! str_contains($this->hostname, ':');
        $easyConnectablePort       = ($this->port !== '') && ctype_digit((string) $this->port) ? ':' . $this->port : '';
        $easyConnectableDatabase   = $this->database !== '' ? '/' . ltrim($this->database, '/') : '';

        if ($isEasyConnectableHostName && ($easyConnectablePort !== '' || $easyConnectableDatabase !== '')) {
            /* If the hostname field isn't empty, doesn't contain
             * ':' and/or '/' and if port and/or database aren't
             * empty, then the hostname field is most likely indeed
             * just a hostname. Therefore we'll try and build an
             * Easy Connect string from these 3 settings, assuming
             * that the database field is a service name.
             */
            $this->DSN = $this->hostname . $easyConnectablePort . $easyConnectableDatabase;

            if (preg_match($this->validDSNs['ec'], $this->DSN)) {
                return;
            }
        }

        /* At this point, we can only try and validate the hostname and
         * database fields separately as DSNs.
         */
        if (preg_match($this->validDSNs['ec'], $this->hostname) || preg_match($this->validDSNs['in'], $this->hostname)) {
            $this->DSN = $this->hostname;

            return;
        }

        $this->database = str_replace(["\n", "\r", "\t", ' '], '', $this->database);

        foreach ($this->validDSNs as $regexp) {
            if (preg_match($regexp, $this->database)) {
                return;
            }
        }

        /* Well - OK, an empty string should work as well.
         * PHP will try to use environment variables to
         * determine which Oracle instance to connect to.
         */
        $this->DSN = '';
    }

    /**
     * Begin Transaction
     */
    protected function _transBegin(): bool
    {
        $this->commitMode = OCI_NO_AUTO_COMMIT;

        return true;
    }

    /**
     * Commit Transaction
     */
    protected function _transCommit(): bool
    {
        $this->commitMode = OCI_COMMIT_ON_SUCCESS;

        return oci_commit($this->connID);
    }

    /**
     * Rollback Transaction
     */
    protected function _transRollback(): bool
    {
        $this->commitMode = OCI_COMMIT_ON_SUCCESS;

        return oci_rollback($this->connID);
    }

    /**
     * Returns the name of the current database being used.
     */
    public function getDatabase(): string
    {
        if (! empty($this->database)) {
            return $this->database;
        }

        return $this->query('SELECT DEFAULT_TABLESPACE FROM USER_USERS')->getRow()->DEFAULT_TABLESPACE ?? '';
    }

    /**
     * Get the prefix of the function to access the DB.
     */
    protected function getDriverFunctionPrefix(): string
    {
        return 'oci_';
    }
}
