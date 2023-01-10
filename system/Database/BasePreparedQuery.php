<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database;

use ArgumentCountError;
use BadMethodCallException;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Events\Events;
use ErrorException;

/**
 * @template TConnection of object|resource
 * @template TStatement of object|resource
 * @template TResult of object|resource
 *
 * @implements PreparedQueryInterface<TConnection, TStatement, TResult>
 */
abstract class BasePreparedQuery implements PreparedQueryInterface
{
    /**
     * The prepared statement itself.
     *
     * @var object|resource|null
     * @phpstan-var TStatement|null
     */
    protected $statement;

    /**
     * The error code, if any.
     *
     * @var int
     */
    protected $errorCode;

    /**
     * The error message, if any.
     *
     * @var string
     */
    protected $errorString;

    /**
     * Holds the prepared query object
     * that is cloned during execute.
     *
     * @var Query
     */
    protected $query;

    /**
     * A reference to the db connection to use.
     *
     * @var BaseConnection
     * @phpstan-var BaseConnection<TConnection, TResult>
     */
    protected $db;

    public function __construct(BaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * Prepares the query against the database, and saves the connection
     * info necessary to execute the query later.
     *
     * NOTE: This version is based on SQL code. Child classes should
     * override this method.
     *
     * @return $this
     */
    public function prepare(string $sql, array $options = [], string $queryClass = Query::class)
    {
        // We only supports positional placeholders (?)
        // in order to work with the execute method below, so we
        // need to replace our named placeholders (:name)
        $sql = preg_replace('/:[^\s,)]+/', '?', $sql);

        /** @var Query $query */
        $query = new $queryClass($this->db);

        $query->setQuery($sql);

        if (! empty($this->db->swapPre) && ! empty($this->db->DBPrefix)) {
            $query->swapPrefix($this->db->DBPrefix, $this->db->swapPre);
        }

        $this->query = $query;

        return $this->_prepare($query->getOriginalQuery(), $options);
    }

    /**
     * The database-dependent portion of the prepare statement.
     *
     * @return $this
     */
    abstract public function _prepare(string $sql, array $options = []);

    /**
     * Takes a new set of data and runs it against the currently
     * prepared query. Upon success, will return a Results object.
     *
     * @return bool|ResultInterface
     * @phpstan-return bool|ResultInterface<TConnection, TResult>
     *
     * @throws DatabaseException
     */
    public function execute(...$data)
    {
        // Execute the Query.
        $startTime = microtime(true);

        try {
            $exception = null;
            $result    = $this->_execute($data);
        } catch (ArgumentCountError|ErrorException $exception) {
            $result = false;
        }

        // Update our query object
        $query = clone $this->query;
        $query->setBinds($data);

        if ($result === false) {
            $query->setDuration($startTime, $startTime);

            // This will trigger a rollback if transactions are being used
            if ($this->db->transDepth !== 0) {
                $this->db->transStatus = false;
            }

            if ($this->db->DBDebug) {
                // We call this function in order to roll-back queries
                // if transactions are enabled. If we don't call this here
                // the error message will trigger an exit, causing the
                // transactions to remain in limbo.
                while ($this->db->transDepth !== 0) {
                    $transDepth = $this->db->transDepth;
                    $this->db->transComplete();

                    if ($transDepth === $this->db->transDepth) {
                        log_message('error', 'Database: Failure during an automated transaction commit/rollback!');
                        break;
                    }
                }

                // Let others do something with this query.
                Events::trigger('DBQuery', $query);

                if ($exception !== null) {
                    throw new DatabaseException($exception->getMessage(), $exception->getCode(), $exception);
                }

                return false;
            }

            // Let others do something with this query.
            Events::trigger('DBQuery', $query);

            return false;
        }

        $query->setDuration($startTime);

        // Let others do something with this query
        Events::trigger('DBQuery', $query);

        if ($this->db->isWriteType($query)) {
            return true;
        }

        // Return a result object
        $resultClass = str_replace('PreparedQuery', 'Result', static::class);

        $resultID = $this->_getResult();

        return new $resultClass($this->db->connID, $resultID);
    }

    /**
     * The database dependant version of the execute method.
     */
    abstract public function _execute(array $data): bool;

    /**
     * Returns the result object for the prepared query.
     *
     * @return object|resource|null
     */
    abstract public function _getResult();

    /**
     * Explicitly closes the prepared statement.
     *
     * @throws BadMethodCallException
     */
    public function close(): bool
    {
        if (! isset($this->statement)) {
            throw new BadMethodCallException('Cannot call close on a non-existing prepared statement.');
        }

        try {
            return $this->_close();
        } finally {
            $this->statement = null;
        }
    }

    /**
     * The database-dependent version of the close method.
     */
    abstract protected function _close(): bool;

    /**
     * Returns the SQL that has been prepared.
     */
    public function getQueryString(): string
    {
        if (! $this->query instanceof QueryInterface) {
            throw new BadMethodCallException('Cannot call getQueryString on a prepared query until after the query has been prepared.');
        }

        return $this->query->getQuery();
    }

    /**
     * A helper to determine if any error exists.
     */
    public function hasError(): bool
    {
        return ! empty($this->errorString);
    }

    /**
     * Returns the error code created while executing this statement.
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * Returns the error message created while executing this statement.
     */
    public function getErrorMessage(): string
    {
        return $this->errorString;
    }
}
