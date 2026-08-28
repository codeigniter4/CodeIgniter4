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

use CodeIgniter\Database\BasePreparedQuery;
use CodeIgniter\Exceptions\BadMethodCallException;
use mysqli;
use mysqli_result;
use mysqli_sql_exception;
use mysqli_stmt;

/**
 * Prepared query for MySQLi
 *
 * @extends BasePreparedQuery<mysqli, mysqli_stmt, mysqli_result, Connection>
 */
class PreparedQuery extends BasePreparedQuery
{
    public function _prepare(string $sql, array $options = []): PreparedQuery
    {
        // Mysqli driver doesn't like statements
        // with terminating semicolons.
        $sql = rtrim($sql, ';');

        if (! $this->statement = $this->db->mysqli->prepare($sql)) {
            $this->errorCode   = $this->db->mysqli->errno;
            $this->errorString = $this->db->mysqli->error;

            if ($this->db->DBDebug) {
                throw $this->db->createDatabaseException($this->errorString, $this->errorCode);
            }
        }

        return $this;
    }

    public function _execute(array $data): bool
    {
        if (! isset($this->statement)) {
            throw new BadMethodCallException('You must call prepare before trying to execute a prepared statement.');
        }

        // First off - bind the parameters
        $bindTypes  = '';
        $binaryData = [];

        // Determine the type string
        foreach ($data as $key => $item) {
            if (is_int($item)) {
                $bindTypes .= 'i';
            } elseif (is_numeric($item)) {
                $bindTypes .= 'd';
            } elseif (is_string($item) && $this->isBinary($item)) {
                $bindTypes .= 'b';
                $binaryData[$key] = $item;
            } else {
                $bindTypes .= 's';
            }
        }

        // Bind it
        $this->statement->bind_param($bindTypes, ...$data);

        // Stream binary data
        foreach ($binaryData as $key => $value) {
            $this->statement->send_long_data($key, $value);
        }

        try {
            $result = $this->statement->execute();
        } catch (mysqli_sql_exception $e) {
            $this->errorCode         = $e->getCode();
            $this->errorString       = $e->getMessage();
            $this->databaseException = $this->db->createDatabaseException($this->errorString, $this->errorCode, $e);

            if ($this->db->DBDebug) {
                throw $this->databaseException;
            }

            return false;
        }

        if ($result === false) {
            $this->errorCode   = $this->statement->errno;
            $this->errorString = $this->statement->error;

            if ($this->db->DBDebug) {
                throw $this->db->createDatabaseException($this->errorString, $this->errorCode);
            }
        }

        return $result;
    }

    /**
     * @return false|mysqli_result
     */
    public function _getResult()
    {
        return $this->statement->get_result();
    }

    protected function _close(): bool
    {
        return $this->statement->close();
    }
}
