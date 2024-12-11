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

namespace CodeIgniter\Database\MySQLi;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use LogicException;
use mysqli;
use mysqli_result;
use mysqli_sql_exception;
use stdClass;
use Throwable;

/**
 * Connection for MySQLi
 *
 * @extends BaseConnection<mysqli, mysqli_result>
 */
class Connection extends BaseConnection
{
    /**
     * Database driver
     *
     * @var string
     */
    public $DBDriver = 'MySQLi';

    /**
     * DELETE hack flag
     *
     * Whether to use the MySQL "delete hack" which allows the number
     * of affected rows to be shown. Uses a preg_replace when enabled,
     * adding a bit more processing to all queries.
     *
     * @var bool
     */
    public $deleteHack = true;

    /**
     * Identifier escape character
     *
     * @var string
     */
    public $escapeChar = '`';

    /**
     * MySQLi object
     *
     * Has to be preserved without being assigned to $connId.
     *
     * @var false|mysqli
     */
    public $mysqli;

    /**
     * MySQLi constant
     *
     * For unbuffered queries use `MYSQLI_USE_RESULT`.
     *
     * Default mode for buffered queries uses `MYSQLI_STORE_RESULT`.
     *
     * @var int
     */
    public $resultMode = MYSQLI_STORE_RESULT;

    /**
     * Use MYSQLI_OPT_INT_AND_FLOAT_NATIVE
     *
     * @var bool
     */
    public $numberNative = false;

    /**
     * Connect to the database.
     *
     * @return false|mysqli
     *
     * @throws DatabaseException
     */
    public function connect(bool $persistent = false)
    {
        // Do we have a socket path?
        if ($this->hostname[0] === '/') {
            $hostname = null;
            $port     = null;
            $socket   = $this->hostname;
        } else {
            $hostname = $persistent ? 'p:' . $this->hostname : $this->hostname;
            $port     = empty($this->port) ? null : $this->port;
            $socket   = '';
        }

        $clientFlags  = ($this->compress === true) ? MYSQLI_CLIENT_COMPRESS : 0;
        $this->mysqli = mysqli_init();

        mysqli_report(MYSQLI_REPORT_ALL & ~MYSQLI_REPORT_INDEX);

        $this->mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

        if ($this->numberNative === true) {
            $this->mysqli->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
        }

        if (isset($this->strictOn)) {
            if ($this->strictOn) {
                $this->mysqli->options(
                    MYSQLI_INIT_COMMAND,
                    "SET SESSION sql_mode = CONCAT(@@sql_mode, ',', 'STRICT_ALL_TABLES')"
                );
            } else {
                $this->mysqli->options(
                    MYSQLI_INIT_COMMAND,
                    "SET SESSION sql_mode = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                                        @@sql_mode,
                                        'STRICT_ALL_TABLES,', ''),
                                    ',STRICT_ALL_TABLES', ''),
                                'STRICT_ALL_TABLES', ''),
                            'STRICT_TRANS_TABLES,', ''),
                        ',STRICT_TRANS_TABLES', ''),
                    'STRICT_TRANS_TABLES', '')"
                );
            }
        }

        if (is_array($this->encrypt)) {
            $ssl = [];

            if (! empty($this->encrypt['ssl_key'])) {
                $ssl['key'] = $this->encrypt['ssl_key'];
            }
            if (! empty($this->encrypt['ssl_cert'])) {
                $ssl['cert'] = $this->encrypt['ssl_cert'];
            }
            if (! empty($this->encrypt['ssl_ca'])) {
                $ssl['ca'] = $this->encrypt['ssl_ca'];
            }
            if (! empty($this->encrypt['ssl_capath'])) {
                $ssl['capath'] = $this->encrypt['ssl_capath'];
            }
            if (! empty($this->encrypt['ssl_cipher'])) {
                $ssl['cipher'] = $this->encrypt['ssl_cipher'];
            }

            if ($ssl !== []) {
                if (isset($this->encrypt['ssl_verify'])) {
                    if ($this->encrypt['ssl_verify']) {
                        if (defined('MYSQLI_OPT_SSL_VERIFY_SERVER_CERT')) {
                            $this->mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, 1);
                        }
                    }
                    // Apparently (when it exists), setting MYSQLI_OPT_SSL_VERIFY_SERVER_CERT
                    // to FALSE didn't do anything, so PHP 5.6.16 introduced yet another
                    // constant ...
                    //
                    // https://secure.php.net/ChangeLog-5.php#5.6.16
                    // https://bugs.php.net/bug.php?id=68344
                    elseif (defined('MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT') && version_compare($this->mysqli->client_info, 'mysqlnd 5.6', '>=')) {
                        $clientFlags += MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
                    }
                }

                $this->mysqli->ssl_set(
                    $ssl['key'] ?? null,
                    $ssl['cert'] ?? null,
                    $ssl['ca'] ?? null,
                    $ssl['capath'] ?? null,
                    $ssl['cipher'] ?? null
                );
            }

            $clientFlags += MYSQLI_CLIENT_SSL;
        }

        try {
            if ($this->mysqli->real_connect(
                $hostname,
                $this->username,
                $this->password,
                $this->database,
                $port,
                $socket,
                $clientFlags
            )) {
                // Prior to version 5.7.3, MySQL silently downgrades to an unencrypted connection if SSL setup fails
                if (($clientFlags & MYSQLI_CLIENT_SSL) !== 0 && version_compare($this->mysqli->client_info, 'mysqlnd 5.7.3', '<=')
                    && empty($this->mysqli->query("SHOW STATUS LIKE 'ssl_cipher'")->fetch_object()->Value)
                ) {
                    $this->mysqli->close();
                    $message = 'MySQLi was configured for an SSL connection, but got an unencrypted connection instead!';
                    log_message('error', $message);

                    if ($this->DBDebug) {
                        throw new DatabaseException($message);
                    }

                    return false;
                }

                if (! $this->mysqli->set_charset($this->charset)) {
                    log_message('error', "Database: Unable to set the configured connection charset ('{$this->charset}').");

                    $this->mysqli->close();

                    if ($this->DBDebug) {
                        throw new DatabaseException('Unable to set client connection character set: ' . $this->charset);
                    }

                    return false;
                }

                return $this->mysqli;
            }
        } catch (Throwable $e) {
            // Clean sensitive information from errors.
            $msg = $e->getMessage();

            $msg = str_replace($this->username, '****', $msg);
            $msg = str_replace($this->password, '****', $msg);

            throw new DatabaseException($msg, $e->getCode(), $e);
        }

        return false;
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
        $this->connID->close();
    }

    /**
     * Select a specific database table to use.
     */
    public function setDatabase(string $databaseName): bool
    {
        if ($databaseName === '') {
            $databaseName = $this->database;
        }

        if (empty($this->connID)) {
            $this->initialize();
        }

        if ($this->connID->select_db($databaseName)) {
            $this->database = $databaseName;

            return true;
        }

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

        if (empty($this->mysqli)) {
            $this->initialize();
        }

        return $this->dataCache['version'] = $this->mysqli->server_info;
    }

    /**
     * Executes the query against the database.
     *
     * @return false|mysqli_result
     */
    protected function execute(string $sql)
    {
        while ($this->connID->more_results()) {
            $this->connID->next_result();
            if ($res = $this->connID->store_result()) {
                $res->free();
            }
        }

        try {
            return $this->connID->query($this->prepQuery($sql), $this->resultMode);
        } catch (mysqli_sql_exception $e) {
            log_message('error', (string) $e);

            if ($this->DBDebug) {
                throw new DatabaseException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return false;
    }

    /**
     * Prep the query. If needed, each database adapter can prep the query string
     */
    protected function prepQuery(string $sql): string
    {
        // mysqli_affected_rows() returns 0 for "DELETE FROM TABLE" queries. This hack
        // modifies the query so that it a proper number of affected rows is returned.
        if ($this->deleteHack === true && preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql)) {
            return trim($sql) . ' WHERE 1=1';
        }

        return $sql;
    }

    /**
     * Returns the total number of rows affected by this query.
     */
    public function affectedRows(): int
    {
        return $this->connID->affected_rows ?? 0;
    }

    /**
     * Platform-dependant string escape
     */
    protected function _escapeString(string $str): string
    {
        if (! $this->connID) {
            $this->initialize();
        }

        return $this->connID->real_escape_string($str);
    }

    /**
     * Escape Like String Direct
     * There are a few instances where MySQLi queries cannot take the
     * additional "ESCAPE x" parameter for specifying the escape character
     * in "LIKE" strings, and this handles those directly with a backslash.
     *
     * @param list<string>|string $str Input string
     *
     * @return list<string>|string
     */
    public function escapeLikeStringDirect($str)
    {
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = $this->escapeLikeStringDirect($val);
            }

            return $str;
        }

        $str = $this->_escapeString($str);

        // Escape LIKE condition wildcards
        return str_replace(
            [$this->likeEscapeChar, '%', '_'],
            ['\\' . $this->likeEscapeChar, '\\%', '\\_'],
            $str
        );
    }

    /**
     * Generates the SQL for listing tables in a platform-dependent manner.
     * Uses escapeLikeStringDirect().
     *
     * @param string|null $tableName If $tableName is provided will return only this table if exists.
     */
    protected function _listTables(bool $prefixLimit = false, ?string $tableName = null): string
    {
        $sql = 'SHOW TABLES FROM ' . $this->escapeIdentifier($this->database);

        if ((string) $tableName !== '') {
            return $sql . ' LIKE ' . $this->escape($tableName);
        }

        if ($prefixLimit && $this->DBPrefix !== '') {
            return $sql . " LIKE '" . $this->escapeLikeStringDirect($this->DBPrefix) . "%'";
        }

        return $sql;
    }

    /**
     * Generates a platform-specific query string so that the column names can be fetched.
     */
    protected function _listColumns(string $table = ''): string
    {
        return 'SHOW COLUMNS FROM ' . $this->protectIdentifiers($table, true, null, false);
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
        $table = $this->protectIdentifiers($table, true, null, false);

        if (($query = $this->query('SHOW COLUMNS FROM ' . $table)) === false) {
            throw new DatabaseException(lang('Database.failGetFieldData'));
        }
        $query = $query->getResultObject();

        $retVal = [];

        for ($i = 0, $c = count($query); $i < $c; $i++) {
            $retVal[$i]       = new stdClass();
            $retVal[$i]->name = $query[$i]->Field;

            sscanf($query[$i]->Type, '%[a-z](%d)', $retVal[$i]->type, $retVal[$i]->max_length);

            $retVal[$i]->nullable    = $query[$i]->Null === 'YES';
            $retVal[$i]->default     = $query[$i]->Default;
            $retVal[$i]->primary_key = (int) ($query[$i]->Key === 'PRI');
        }

        return $retVal;
    }

    /**
     * Returns an array of objects with index data
     *
     * @return array<string, stdClass>
     *
     * @throws DatabaseException
     * @throws LogicException
     */
    protected function _indexData(string $table): array
    {
        $table = $this->protectIdentifiers($table, true, null, false);

        if (($query = $this->query('SHOW INDEX FROM ' . $table)) === false) {
            throw new DatabaseException(lang('Database.failGetIndexData'));
        }

        $indexes = $query->getResultArray();

        if ($indexes === []) {
            return [];
        }

        $keys = [];

        foreach ($indexes as $index) {
            if (empty($keys[$index['Key_name']])) {
                $keys[$index['Key_name']]       = new stdClass();
                $keys[$index['Key_name']]->name = $index['Key_name'];

                if ($index['Key_name'] === 'PRIMARY') {
                    $type = 'PRIMARY';
                } elseif ($index['Index_type'] === 'FULLTEXT') {
                    $type = 'FULLTEXT';
                } elseif ($index['Non_unique']) {
                    $type = $index['Index_type'] === 'SPATIAL' ? 'SPATIAL' : 'INDEX';
                } else {
                    $type = 'UNIQUE';
                }

                $keys[$index['Key_name']]->type = $type;
            }

            $keys[$index['Key_name']]->fields[] = $index['Column_name'];
        }

        return $keys;
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
        $sql = '
                SELECT
                    tc.CONSTRAINT_NAME,
                    tc.TABLE_NAME,
                    kcu.COLUMN_NAME,
                    rc.REFERENCED_TABLE_NAME,
                    kcu.REFERENCED_COLUMN_NAME,
                    rc.DELETE_RULE,
                    rc.UPDATE_RULE,
                    rc.MATCH_OPTION
                FROM information_schema.table_constraints AS tc
                INNER JOIN information_schema.referential_constraints AS rc
                    ON tc.constraint_name = rc.constraint_name
                    AND tc.constraint_schema = rc.constraint_schema
                INNER JOIN information_schema.key_column_usage AS kcu
                    ON tc.constraint_name = kcu.constraint_name
                    AND tc.constraint_schema = kcu.constraint_schema
                WHERE
                    tc.constraint_type = ' . $this->escape('FOREIGN KEY') . ' AND
                    tc.table_schema = ' . $this->escape($this->database) . ' AND
                    tc.table_name = ' . $this->escape($table);

        if (($query = $this->query($sql)) === false) {
            throw new DatabaseException(lang('Database.failGetForeignKeyData'));
        }

        $query   = $query->getResultObject();
        $indexes = [];

        foreach ($query as $row) {
            $indexes[$row->CONSTRAINT_NAME]['constraint_name']       = $row->CONSTRAINT_NAME;
            $indexes[$row->CONSTRAINT_NAME]['table_name']            = $row->TABLE_NAME;
            $indexes[$row->CONSTRAINT_NAME]['column_name'][]         = $row->COLUMN_NAME;
            $indexes[$row->CONSTRAINT_NAME]['foreign_table_name']    = $row->REFERENCED_TABLE_NAME;
            $indexes[$row->CONSTRAINT_NAME]['foreign_column_name'][] = $row->REFERENCED_COLUMN_NAME;
            $indexes[$row->CONSTRAINT_NAME]['on_delete']             = $row->DELETE_RULE;
            $indexes[$row->CONSTRAINT_NAME]['on_update']             = $row->UPDATE_RULE;
            $indexes[$row->CONSTRAINT_NAME]['match']                 = $row->MATCH_OPTION;
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
        return 'SET FOREIGN_KEY_CHECKS=0';
    }

    /**
     * Returns platform-specific SQL to enable foreign key checks.
     *
     * @return string
     */
    protected function _enableForeignKeyChecks()
    {
        return 'SET FOREIGN_KEY_CHECKS=1';
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
        if (! empty($this->mysqli->connect_errno)) {
            return [
                'code'    => $this->mysqli->connect_errno,
                'message' => $this->mysqli->connect_error,
            ];
        }

        return [
            'code'    => $this->connID->errno,
            'message' => $this->connID->error,
        ];
    }

    /**
     * Insert ID
     */
    public function insertID(): int
    {
        return $this->connID->insert_id;
    }

    /**
     * Begin Transaction
     */
    protected function _transBegin(): bool
    {
        $this->connID->autocommit(false);

        return $this->connID->begin_transaction();
    }

    /**
     * Commit Transaction
     */
    protected function _transCommit(): bool
    {
        if ($this->connID->commit()) {
            $this->connID->autocommit(true);

            return true;
        }

        return false;
    }

    /**
     * Rollback Transaction
     */
    protected function _transRollback(): bool
    {
        if ($this->connID->rollback()) {
            $this->connID->autocommit(true);

            return true;
        }

        return false;
    }
}
