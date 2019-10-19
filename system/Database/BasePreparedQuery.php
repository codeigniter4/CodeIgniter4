<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Database;

use CodeIgniter\Database\MySQLi\Connection;
use CodeIgniter\Events\Events;

/**
 * Base prepared query
 */
abstract class BasePreparedQuery implements PreparedQueryInterface
{

	/**
	 * The prepared statement itself.
	 *
	 * @var resource|\mysqli_stmt
	 */
	protected $statement;

	/**
	 * The error code, if any.
	 *
	 * @var integer
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
	 * @var BaseConnection|MySQLi\Connection
	 */
	protected $db;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param \CodeIgniter\Database\ConnectionInterface $db
	 */
	public function __construct(ConnectionInterface $db)
	{
		$this->db = &$db;
	}

	//--------------------------------------------------------------------

	/**
	 * Prepares the query against the database, and saves the connection
	 * info necessary to execute the query later.
	 *
	 * NOTE: This version is based on SQL code. Child classes should
	 * override this method.
	 *
	 * @param string $sql
	 * @param array  $options    Passed to the connection's prepare statement.
	 * @param string $queryClass
	 *
	 * @return mixed
	 */
	public function prepare(string $sql, array $options = [], string $queryClass = 'CodeIgniter\\Database\\Query')
	{
		// We only supports positional placeholders (?)
		// in order to work with the execute method below, so we
		// need to replace our named placeholders (:name)
		$sql = preg_replace('/:[^\s,)]+/', '?', $sql);

		/**
		 * @var \CodeIgniter\Database\Query $query
		 */
		$query = new $queryClass($this->db);

		$query->setQuery($sql);

		if (! empty($this->db->swapPre) && ! empty($this->db->DBPrefix))
		{
			$query->swapPrefix($this->db->DBPrefix, $this->db->swapPre);
		}

		$this->query = $query;

		return $this->_prepare($query->getOriginalQuery(), $options);
	}

	//--------------------------------------------------------------------

	/**
	 * The database-dependent portion of the prepare statement.
	 *
	 * @param string $sql
	 * @param array  $options Passed to the connection's prepare statement.
	 *
	 * @return mixed
	 */
	abstract public function _prepare(string $sql, array $options = []);

	//--------------------------------------------------------------------

	/**
	 * Takes a new set of data and runs it against the currently
	 * prepared query. Upon success, will return a Results object.
	 *
	 * @param array $data
	 *
	 * @return ResultInterface
	 */
	public function execute(...$data)
	{
		// Execute the Query.
		$startTime = microtime(true);

		$result = $this->_execute($data);

		// Update our query object
		$query = clone $this->query;
		$query->setBinds($data);

		$query->setDuration($startTime);

		// Let others do something with this query
		Events::trigger('DBQuery', $query);

		// Return a result object
		$resultClass = str_replace('PreparedQuery', 'Result', get_class($this));

		$resultID = $this->_getResult();

		return new $resultClass($this->db->connID, $resultID);
	}

	//--------------------------------------------------------------------

	/**
	 * The database dependant version of the execute method.
	 *
	 * @param array $data
	 *
	 * @return boolean
	 */
	abstract public function _execute(array $data): bool;

	//--------------------------------------------------------------------

	/**
	 * Returns the result object for the prepared query.
	 *
	 * @return mixed
	 */
	abstract public function _getResult();

	//--------------------------------------------------------------------

	/**
	 * Explicitly closes the statement.
	 *
	 * @return null|void
	 */
	public function close()
	{
		if (! is_object($this->statement))
		{
			return;
		}

		$this->statement->close();
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the SQL that has been prepared.
	 *
	 * @return string
	 */
	public function getQueryString(): string
	{
		if (! $this->query instanceof QueryInterface)
		{
			throw new \BadMethodCallException('Cannot call getQueryString on a prepared query until after the query has been prepared.');
		}

		return $this->query->getQuery();
	}

	//--------------------------------------------------------------------

	/**
	 * A helper to determine if any error exists.
	 *
	 * @return boolean
	 */
	public function hasError(): bool
	{
		return ! empty($this->errorString);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the error code created while executing this statement.
	 *
	 * @return integer
	 */
	public function getErrorCode(): int
	{
		return $this->errorCode;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the error message created while executing this statement.
	 *
	 * @return string
	 */
	public function getErrorMessage(): string
	{
		return $this->errorString;
	}

	//--------------------------------------------------------------------
}
