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
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * Prepared query for OCI8
 *
 * @extends BasePreparedQuery<resource, resource, resource>
 */
class PreparedQuery extends BasePreparedQuery
{
    /**
     * A reference to the db connection to use.
     *
     * @var Connection
     */
    protected $db;

    /**
     * Latest inserted table name.
     */
    private ?string $lastInsertTableName = null;

    /**
     * Prepares the query against the database, and saves the connection
     * info necessary to execute the query later.
     *
     * NOTE: This version is based on SQL code. Child classes should
     * override this method.
     *
     * @param array $options Passed to the connection's prepare statement.
     *                       Unused in the OCI8 driver.
     */
    public function _prepare(string $sql, array $options = []): PreparedQuery
    {
        if (! $this->statement = oci_parse($this->db->connID, $this->parameterize($sql))) {
            $error             = oci_error($this->db->connID);
            $this->errorCode   = $error['code'] ?? 0;
            $this->errorString = $error['message'] ?? '';

            if ($this->db->DBDebug) {
                throw new DatabaseException($this->errorString . ' code: ' . $this->errorCode);
            }
        }

        $this->lastInsertTableName = $this->db->parseInsertTableName($sql);

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

        foreach (array_keys($data) as $key) {
            oci_bind_by_name($this->statement, ':' . $key, $data[$key]);
        }

        $result = oci_execute($this->statement, $this->db->commitMode);

        if ($result && $this->lastInsertTableName !== '') {
            $this->db->lastInsertedTableName = $this->lastInsertTableName;
        }

        return $result;
    }

    /**
     * Returns the statement resource for the prepared query or false when preparing failed.
     *
     * @return resource|null
     */
    public function _getResult()
    {
        return $this->statement;
    }

    /**
     * Deallocate prepared statements.
     */
    protected function _close(): bool
    {
        return oci_free_statement($this->statement);
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
