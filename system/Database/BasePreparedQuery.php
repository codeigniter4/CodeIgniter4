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
use BackedEnum;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\BadMethodCallException;
use ErrorException;
use Throwable;

/**
 * @template TConnection
 * @template TStatement
 * @template TResult
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
    protected $errorString;

    /**
     * The typed exception for the last failed prepared query, if any.
     */
    protected ?DatabaseException $databaseException = null;

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
     * @var BaseConnection<TConnection, TResult>
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
        // We only support positional placeholders (?), so convert
        // named placeholders (:name or :name:) while leaving dialect
        // syntax like PostgreSQL casts (::type) untouched.
        $sql = preg_replace('/(?<!:):([a-zA-Z_]\w*):?(?!:)/', '?', $sql);

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
     * @return bool|ResultInterface<TConnection, TResult>
     *
     * @throws DatabaseException
     */
    public function execute(...$data)
    {
        foreach ($data as $key => $value) {
            if ($value instanceof BackedEnum) {
                $data[$key] = $value->value;
            }
        }

        // Execute the Query.
        $startTime = microtime(true);

        try {
            $exception = null;
            $this->db->setLastException(null);
            $this->databaseException = null;
            $result                  = $this->_execute($data);
        } catch (ArgumentCountError|DatabaseException|ErrorException $exception) {
            $result = false;
        }

        // Update our query object
        $query = clone $this->query;
        $query->setBinds($data);

        if ($result === false) {
            $query->setDuration($startTime, $startTime);

            $databaseException = $this->createDatabaseException($exception);

            // This will trigger a rollback if transactions are being used
            $this->db->handleTransStatus($databaseException);

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

                if ($databaseException instanceof DatabaseException) {
                    throw $databaseException;
                }

                return false;
            }

            // Let others do something with this query.
            Events::trigger('DBQuery', $query);

            $this->db->setLastException($databaseException);

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
     * The database dependant version of the execute method.
     */
    abstract public function _execute(array $data): bool;

    /**
     * Returns the result object for the prepared query.
     *
     * @return false|object|resource|null
     */
    abstract public function _getResult();

    /**
     * Creates the database exception for a failed prepared query.
     */
    private function createDatabaseException(?Throwable $previous): ?DatabaseException
    {
        if ($previous instanceof DatabaseException) {
            return $previous;
        }

        if ($this->databaseException instanceof DatabaseException) {
            return $this->databaseException;
        }

        if ($previous instanceof Throwable) {
            return $this->db->createDatabaseException(
                $previous->getMessage(),
                $previous->getCode(),
                $previous,
            );
        }

        if ($this->errorString === null || $this->errorString === '') {
            return null;
        }

        return $this->db->createDatabaseException($this->errorString, $this->errorCode);
    }

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

    /**
     * Whether the input contain binary data.
     */
    protected function isBinary(string $input): bool
    {
        return mb_detect_encoding($input, 'UTF-8', true) === false;
    }
}
