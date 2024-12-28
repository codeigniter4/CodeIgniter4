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

namespace CodeIgniter\Database\Postgre;

use CodeIgniter\Database\BasePreparedQuery;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\BadMethodCallException;
use Exception;
use PgSql\Connection as PgSqlConnection;
use PgSql\Result as PgSqlResult;

/**
 * Prepared query for Postgre
 *
 * @extends BasePreparedQuery<PgSqlConnection, PgSqlResult, PgSqlResult>
 */
class PreparedQuery extends BasePreparedQuery
{
    /**
     * Stores the name this query can be
     * used under by postgres. Only used internally.
     *
     * @var string
     */
    protected $name;

    /**
     * The result resource from a successful
     * pg_exec. Or false.
     *
     * @var false|PgSqlResult
     */
    protected $result;

    /**
     * Prepares the query against the database, and saves the connection
     * info necessary to execute the query later.
     *
     * NOTE: This version is based on SQL code. Child classes should
     * override this method.
     *
     * @param array $options Passed to the connection's prepare statement.
     *                       Unused in the MySQLi driver.
     *
     * @throws Exception
     */
    public function _prepare(string $sql, array $options = []): PreparedQuery
    {
        $this->name = (string) random_int(1, 10_000_000_000_000_000);

        $sql = $this->parameterize($sql);

        // Update the query object since the parameters are slightly different
        // than what was put in.
        $this->query->setQuery($sql);

        if (! $this->statement = pg_prepare($this->db->connID, $this->name, $sql)) {
            $this->errorCode   = 0;
            $this->errorString = pg_last_error($this->db->connID);

            if ($this->db->DBDebug) {
                throw new DatabaseException($this->errorString . ' code: ' . $this->errorCode);
            }
        }

        return $this;
    }

    /**
     * Takes a new set of data and runs it against the currently
     * prepared query. Upon success, will return a Results object.
     */
    public function _execute(array $data): bool
    {
        if (! isset($this->statement)) {
            throw new BadMethodCallException('You must call prepare before trying to execute a prepared statement.');
        }

        foreach ($data as &$item) {
            if (is_string($item) && $this->isBinary($item)) {
                $item = pg_escape_bytea($this->db->connID, $item);
            }
        }

        $this->result = pg_execute($this->db->connID, $this->name, $data);

        return (bool) $this->result;
    }

    /**
     * Returns the result object for the prepared query or false on failure.
     *
     * @return PgSqlResult|null
     */
    public function _getResult()
    {
        return $this->result;
    }

    /**
     * Deallocate prepared statements.
     */
    protected function _close(): bool
    {
        return pg_query($this->db->connID, 'DEALLOCATE "' . $this->db->escapeIdentifiers($this->name) . '"') !== false;
    }

    /**
     * Replaces the ? placeholders with $1, $2, etc parameters for use
     * within the prepared query.
     */
    public function parameterize(string $sql): string
    {
        // Track our current value
        $count = 0;

        return preg_replace_callback('/\?/', static function () use (&$count): string {
            $count++;

            return "\${$count}";
        }, $sql);
    }
}
