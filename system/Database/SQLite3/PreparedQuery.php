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

use BadMethodCallException;
use CodeIgniter\Database\BasePreparedQuery;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Exception;
use SQLite3;
use SQLite3Result;
use SQLite3Stmt;

/**
 * Prepared query for SQLite3
 *
 * @extends BasePreparedQuery<SQLite3, SQLite3Stmt, SQLite3Result>
 */
class PreparedQuery extends BasePreparedQuery
{
    /**
     * The SQLite3Result resource, or false.
     *
     * @var false|SQLite3Result
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
     */
    public function _prepare(string $sql, array $options = []): PreparedQuery
    {
        if (! ($this->statement = $this->db->connID->prepare($sql))) {
            $this->errorCode   = $this->db->connID->lastErrorCode();
            $this->errorString = $this->db->connID->lastErrorMsg();

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

        foreach ($data as $key => $item) {
            // Determine the type string
            if (is_int($item)) {
                $bindType = SQLITE3_INTEGER;
            } elseif (is_float($item)) {
                $bindType = SQLITE3_FLOAT;
            } elseif (is_string($item) && $this->isBinary($item)) {
                $bindType = SQLITE3_BLOB;
            } else {
                $bindType = SQLITE3_TEXT;
            }

            // Bind it
            $this->statement->bindValue($key + 1, $item, $bindType);
        }

        try {
            $this->result = $this->statement->execute();
        } catch (Exception $e) {
            if ($this->db->DBDebug) {
                throw new DatabaseException($e->getMessage(), $e->getCode(), $e);
            }

            return false;
        }

        return $this->result !== false;
    }

    /**
     * Returns the result object for the prepared query or false on failure.
     *
     * @return false|SQLite3Result
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
        return $this->statement->close();
    }
}
