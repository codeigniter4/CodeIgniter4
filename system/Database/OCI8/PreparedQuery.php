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

use CodeIgniter\Database\BasePreparedQuery;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\BadMethodCallException;
use ErrorException;
use OCILob;

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
                throw $this->db->createDatabaseException($this->errorString, $this->errorCode);
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

        $binaryData = null;

        foreach (array_keys($data) as $key) {
            if (is_string($data[$key]) && $this->isBinary($data[$key])) {
                $binaryData = oci_new_descriptor($this->db->connID, OCI_D_LOB);
                $binaryData->writeTemporary($data[$key], OCI_TEMP_BLOB);
                oci_bind_by_name($this->statement, ':' . $key, $binaryData, -1, OCI_B_BLOB);
            } else {
                oci_bind_by_name($this->statement, ':' . $key, $data[$key]);
            }
        }

        try {
            $result = oci_execute($this->statement, $this->db->commitMode);
        } catch (ErrorException $e) {
            $databaseException = $this->setDatabaseExceptionFromStatement($e);

            if ($this->db->DBDebug) {
                throw $databaseException;
            }

            return false;
        } finally {
            if ($binaryData instanceof OCILob) {
                $binaryData->free();
            }
        }

        if ($result === false) {
            $databaseException = $this->setDatabaseExceptionFromStatement();

            if ($this->db->DBDebug) {
                throw $databaseException;
            }
        }

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
     * Captures the native OCI statement error for shared database exception classification.
     */
    private function setDatabaseExceptionFromStatement(?ErrorException $previous = null): DatabaseException
    {
        $error                   = oci_error($this->statement);
        $this->errorCode         = $error['code'] ?? 0;
        $this->errorString       = $error['message'] ?? $previous?->getMessage() ?? '';
        $this->databaseException = $this->db->createDatabaseException($this->errorString, $this->errorCode, $previous);

        return $this->databaseException;
    }

    /**
     * Replaces the ? placeholders with :0, :1, etc parameters for use
     * within the prepared query.
     */
    public function parameterize(string $sql): string
    {
        // Track our current value
        $count = 0;

        return preg_replace_callback('/\?/', static function ($matches) use (&$count): string {
            return ':' . ($count++);
        }, $sql);
    }
}
