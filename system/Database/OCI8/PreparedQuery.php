<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\OCI8;

use BadMethodCallException;
use CodeIgniter\Database\BasePreparedQuery;
use CodeIgniter\Database\PreparedQueryInterface;

/**
 * Prepared query for OCI8
 */
class PreparedQuery extends BasePreparedQuery implements PreparedQueryInterface
{
    /**
     * A reference to the db connection to use.
     *
     * @var Connection
     */
    protected $db;

    /**
     * Is collect row id
     *
     * @var bool
     */
    private $isCollectRowId;

    /**
     * Prepares the query against the database, and saves the connection
     * info necessary to execute the query later.
     *
     * NOTE: This version is based on SQL code. Child classes should
     * override this method.
     *
     * @param array $options Passed to the connection's prepare statement.
     *
     * @return mixed
     */
    public function prepare(string $sql, array $options = [], string $queryClass = 'CodeIgniter\\Database\\Query')
    {
        $this->isCollectRowId = false;

        if (substr($sql, strpos($sql, 'RETURNING ROWID INTO :CI_OCI8_ROWID')) === 'RETURNING ROWID INTO :CI_OCI8_ROWID') {
            $this->isCollectRowId = true;
        }

        return parent::prepare($sql, $options, $queryClass);
    }

    /**
     * Prepares the query against the database, and saves the connection
     * info necessary to execute the query later.
     *
     * NOTE: This version is based on SQL code. Child classes should
     * override this method.
     *
     * @param array $options Passed to the connection's prepare statement.
     *                       Unused in the OCI8 driver.
     *
     * @return mixed
     */
    public function _prepare(string $sql, array $options = [])
    {
        $sql = rtrim($sql, ';');
        if (strpos('BEGIN', ltrim($sql)) === 0) {
            $sql .= ';';
        }

        if (! $this->statement = oci_parse($this->db->connID, $this->parameterize($sql))) {
            $error             = oci_error($this->db->connID);
            $this->errorCode   = $error['code'] ?? 0;
            $this->errorString = $error['message'] ?? '';
        }

        return $this;
    }

    /**
     * Takes a new set of data and runs it against the currently
     * prepared query. Upon success, will return a Results object.
     */
    public function _execute(array $data): bool
    {
        if (null === $this->statement) {
            throw new BadMethodCallException('You must call prepare before trying to execute a prepared statement.');
        }

        $lastKey = 0;

        foreach (array_keys($data) as $key) {
            oci_bind_by_name($this->statement, ':' . $key, $data[$key]);
            $lastKey = $key;
        }

        if ($this->isCollectRowId) {
            oci_bind_by_name($this->statement, ':' . (++$lastKey), $this->db->rowId, 255);
        }

        return oci_execute($this->statement, $this->db->commitMode);
    }

    /**
     * Returns the result object for the prepared query.
     *
     * @return mixed
     */
    public function _getResult()
    {
        return $this->statement;
    }

    /**
     * Replaces the ? placeholders with :0, :1, etc parameters for use
     * within the prepared query.
     */
    public function parameterize(string $sql): string
    {
        // Track our current value
        $count = 0;

        return preg_replace_callback('/\?/', static function ($matches) use (&$count) {
            return ':' . ($count++);
        }, $sql);
    }
}
