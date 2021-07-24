<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\MySQLi;

use BadMethodCallException;
use CodeIgniter\Database\BasePreparedQuery;

/**
 * Prepared query for MySQLi
 */
class PreparedQuery extends BasePreparedQuery
{
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
     * @return mixed
     */
    public function _prepare(string $sql, array $options = [])
    {
        // Mysqli driver doesn't like statements
        // with terminating semicolons.
        $sql = rtrim($sql, ';');

        if (! $this->statement = $this->db->mysqli->prepare($sql)) {
            $this->errorCode   = $this->db->mysqli->errno;
            $this->errorString = $this->db->mysqli->error;
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

        // First off -bind the parameters
        $bindTypes = '';

        // Determine the type string
        foreach ($data as $item) {
            if (is_int($item)) {
                $bindTypes .= 'i';
            } elseif (is_numeric($item)) {
                $bindTypes .= 'd';
            } else {
                $bindTypes .= 's';
            }
        }

        // Bind it
        $this->statement->bind_param($bindTypes, ...$data);

        return $this->statement->execute();
    }

    /**
     * Returns the result object for the prepared query.
     *
     * @return mixed
     */
    public function _getResult()
    {
        return $this->statement->get_result();
    }
}
