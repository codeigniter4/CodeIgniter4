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

namespace CodeIgniter\Database;

use ArgumentCountError;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\BadMethodCallException;
use ErrorException;

/**
 * @template TConnection
 * @template TStatement
 * @template TResult
 * @template TDb of BaseConnection
 *
 * @implements PreparedQueryInterface<TConnection, TStatement, TResult>
 */
abstract class BasePreparedQuery implements PreparedQueryInterface
{
    /**
     * The prepared statement itself.
     *
     * @var TStatement|null
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
    protected $errorString = '';

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
     * @var TDb
     */
    protected $db;

    public function __construct(BaseConnection $db)
    {
        $this->db = $db;
    }

    public function prepare(string $sql, array $options = [], string $queryClass = Query::class)
    {
        // We only support positional placeholders (?), so convert
        // named placeholders (:name or :name:) while leaving dialect
        // syntax like PostgreSQL casts (::type) untouched.
        $sql = preg_replace('/(?<!:):([a-zA-Z_]\w*):?(?!:)/', '?', $sql);

        /** @var Query $query */
        $query = new $queryClass($this->db);

        $query->setQuery($sql);

        if ($this->db->swapPre !== '' && $this->db->DBPrefix !== '') {
            $query->swapPrefix($this->db->DBPrefix, $this->db->swapPre);
        }

        $this->query = $query;

        return $this->_prepare($query->getOriginalQuery(), $options);
    }

    /**
     * @param array<array-key, mixed> $options Passed to the connection's prepare statement. Only the SQLSRV driver uses it.
     *
     * @return $this
     */
    abstract public function _prepare(string $sql, array $options = []);

    /**
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
            $this->db->handleTransStatus();

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

        if ($this->db->isWriteType((string) $query)) {
            return true;
        }

        // Return a result object
        $resultClass = str_replace('PreparedQuery', 'Result', static::class);

        $resultID = $this->_getResult();

        return new $resultClass($this->db->connID, $resultID);
    }

    /**
     * @param list<mixed> $data
     */
    abstract public function _execute(array $data): bool;

    /**
     * Returns the result object for the prepared query.
     *
     * @return false|object|resource|null
     */
    abstract public function _getResult();

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

    abstract protected function _close(): bool;

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
        return $this->errorString !== '';
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    public function getErrorMessage(): string
    {
        return $this->errorString;
    }

    /**
     * Whether the input contain binary data.
     */
    protected function isBinary(string $input): bool
    {
        return mb_detect_encoding($input, 'UTF-8', true) === false;
    }
}
