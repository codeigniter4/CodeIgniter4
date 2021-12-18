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

use BadMethodCallException;
use CodeIgniter\Events\Events;

/**
 * Base prepared query
 */
abstract class BasePreparedQuery implements PreparedQueryInterface
{
    /**
     * The prepared statement itself.
     *
     * @var object|resource
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
     */
    protected $db;

    public function __construct(BaseConnection $db)
    {
        $this->db = &$db;
    }

    /**
     * Prepares the query against the database, and saves the connection
     * info necessary to execute the query later.
     *
     * NOTE: This version is based on SQL code. Child classes should
     * override this method.
     *
     * @return mixed
     */
    public function prepare(string $sql, array $options = [], string $queryClass = 'CodeIgniter\\Database\\Query')
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
     * @return mixed
     */
    abstract public function _prepare(string $sql, array $options = []);

    /**
     * Takes a new set of data and runs it against the currently
     * prepared query. Upon success, will return a Results object.
     *
     * @return ResultInterface
     */
    public function execute(...$data)
    {
        // Execute the Query.
        $startTime = microtime(true);

        $this->_execute($data);

        // Update our query object
        $query = clone $this->query;
        $query->setBinds($data);

        $query->setDuration($startTime);

        // Let others do something with this query
        Events::trigger('DBQuery', $query);

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
     * @return mixed
     */
    abstract public function _getResult();

    /**
     * Explicitly closes the statement.
     */
    public function close()
    {
        if (! is_object($this->statement) || ! method_exists($this->statement, 'close')) {
            return;
        }

        $this->statement->close();
    }

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
