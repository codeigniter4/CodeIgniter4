<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use CodeIgniter\CodeIgniter;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\Query;

class MockConnection extends BaseConnection
{
    protected $returnValues = [];

    public $database;

    public $lastQuery;

    public function shouldReturn(string $method, $return)
    {
        $this->returnValues[$method] = $return;

        return $this;
    }

    /**
     * Orchestrates a query against the database. Queries must use
     * Database\Statement objects to store the query and build it.
     * This method works with the cache.
     *
     * Should automatically handle different connections for read/write
     * queries if needed.
     *
     * @param mixed ...$binds
     *
     * @return BaseResult|bool|Query
     *
     * @todo BC set $queryClass default as null in 4.1
     */
    public function query(string $sql, $binds = null, bool $setEscapeFlags = true, string $queryClass = '')
    {
        $queryClass = str_replace('Connection', 'Query', static::class);

        $query = new $queryClass($this);

        $query->setQuery($sql, $binds, $setEscapeFlags);

        if (! empty($this->swapPre) && ! empty($this->DBPrefix)) {
            $query->swapPrefix($this->DBPrefix, $this->swapPre);
        }

        $startTime = microtime(true);

        $this->lastQuery = $query;

        // Run the query
        if (false === ($this->resultID = $this->simpleQuery($query->getQuery()))) {
            $query->setDuration($startTime, $startTime);

            // @todo deal with errors

            return false;
        }

        $query->setDuration($startTime);

        // resultID is not false, so it must be successful
        if ($query->isWriteType()) {
            return true;
        }

        // query is not write-type, so it must be read-type query; return QueryResult
        $resultClass = str_replace('Connection', 'Result', static::class);

        return new $resultClass($this->connID, $this->resultID);
    }

    /**
     * Connect to the database.
     *
     * @return mixed
     */
    public function connect(bool $persistent = false)
    {
        $return = $this->returnValues['connect'] ?? true;

        if (is_array($return)) {
            // By removing the top item here, we can
            // get a different value for, say, testing failover connections.
            $return = array_shift($this->returnValues['connect']);
        }

        return $return;
    }

    /**
     * Keep or establish the connection if no queries have been sent for
     * a length of time exceeding the server's idle timeout.
     */
    public function reconnect(): bool
    {
        return true;
    }

    /**
     * Select a specific database table to use.
     *
     * @return mixed
     */
    public function setDatabase(string $databaseName)
    {
        $this->database = $databaseName;

        return $this;
    }

    /**
     * Returns a string containing the version of the database being used.
     */
    public function getVersion(): string
    {
        return CodeIgniter::CI_VERSION;
    }

    /**
     * Executes the query against the database.
     *
     * @return mixed
     */
    protected function execute(string $sql)
    {
        return $this->returnValues['execute'];
    }

    /**
     * Returns the total number of rows affected by this query.
     */
    public function affectedRows(): int
    {
        return 1;
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
        return [
            'code'    => 0,
            'message' => '',
        ];
    }

    /**
     * Insert ID
     */
    public function insertID(): int
    {
        return $this->connID->insert_id; // @phpstan-ignore-line
    }

    /**
     * Generates the SQL for listing tables in a platform-dependent manner.
     */
    protected function _listTables(bool $constrainByPrefix = false): string
    {
        return '';
    }

    /**
     * Generates a platform-specific query string so that the column names can be fetched.
     */
    protected function _listColumns(string $table = ''): string
    {
        return '';
    }

    protected function _fieldData(string $table): array
    {
        return [];
    }

    protected function _indexData(string $table): array
    {
        return [];
    }

    protected function _foreignKeyData(string $table): array
    {
        return [];
    }

    /**
     * Close the connection.
     */
    protected function _close()
    {
    }

    /**
     * Begin Transaction
     */
    protected function _transBegin(): bool
    {
        return true;
    }

    /**
     * Commit Transaction
     */
    protected function _transCommit(): bool
    {
        return true;
    }

    /**
     * Rollback Transaction
     */
    protected function _transRollback(): bool
    {
        return true;
    }
}
