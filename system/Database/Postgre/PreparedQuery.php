<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Postgre;

use BadMethodCallException;
use CodeIgniter\Database\BasePreparedQuery;
use Exception;

/**
 * Prepared query for Postgre
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
     * @var bool|Result
     */
    protected $result;

    /**
     * Prepares the query against the database, and saves the connection
     * info necessary to execute the query later.
     *
     * NOTE: This version is based on SQL code. Child classes should
     * override this method.
     *
     * @param string $sql
     * @param array  $options Passed to the connection's prepare statement.
     *                        Unused in the MySQLi driver.
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function _prepare(string $sql, array $options = [])
    {
        $this->name = (string) random_int(1, 10000000000000000);

        $sql = $this->parameterize($sql);

        // Update the query object since the parameters are slightly different
        // than what was put in.
        $this->query->setQuery($sql);

        if (! $this->statement = pg_prepare($this->db->connID, $this->name, $sql)) {
            $this->errorCode   = 0;
            $this->errorString = pg_last_error($this->db->connID);
        }

        return $this;
    }

    /**
     * Takes a new set of data and runs it against the currently
     * prepared query. Upon success, will return a Results object.
     *
     * @param array $data
     *
     * @return bool
     */
    public function _execute(array $data): bool
    {
        if (! isset($this->statement)) {
            throw new BadMethodCallException('You must call prepare before trying to execute a prepared statement.');
        }

        $this->result = pg_execute($this->db->connID, $this->name, $data);

        return (bool) $this->result;
    }

    /**
     * Returns the result object for the prepared query.
     *
     * @return mixed
     */
    public function _getResult()
    {
        return $this->result;
    }

    /**
     * Replaces the ? placeholders with $1, $2, etc parameters for use
     * within the prepared query.
     *
     * @param string $sql
     *
     * @return string
     */
    public function parameterize(string $sql): string
    {
        // Track our current value
        $count = 0;

        return preg_replace_callback('/\?/', static function () use (&$count) {
            $count++;

            return "\${$count}";
        }, $sql);
    }
}
