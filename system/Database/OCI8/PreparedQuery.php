<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
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
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Database\OCI8;

use CodeIgniter\Database\PreparedQueryInterface;
use CodeIgniter\Database\BasePreparedQuery;

/**
 * Prepared query for MySQLi
 */
class PreparedQuery extends BasePreparedQuery implements PreparedQueryInterface
{
	private $isCollectRowId;

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
		$this->isCollectRowId = false;

		if (substr($sql, strpos($sql, 'RETURNING ROWID INTO :CI_OCI8_ROWID')) === 'RETURNING ROWID INTO :CI_OCI8_ROWID')
		{
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
	 * @param string $sql
	 * @param array  $options Passed to the connection's prepare statement.
	 *                        Unused in the MySQLi driver.
	 *
	 * @return mixed
	 */
	public function _prepare(string $sql, array $options = [])
	{
		$sql = rtrim($sql, ';');
		if (strpos('BEGIN', ltrim($sql)) === 0)
		{
			$sql .= ';';
		}

		if (! $this->statement = oci_parse($this->db->connID, $this->parameterize($sql)))
		{
			$error             = oci_error($this->db->connID);
			$this->errorCode   = $error['code'] ?? 0;
			$this->errorString = $error['message'] ?? '';
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Takes a new set of data and runs it against the currently
	 * prepared query. Upon success, will return a Results object.
	 *
	 * @param array $data
	 *
	 * @return boolean
	 */
	public function _execute(array $data): bool
	{
		if (is_null($this->statement))
		{
			throw new \BadMethodCallException('You must call prepare before trying to execute a prepared statement.');
		}

		$last_key = 0;
		foreach (array_keys($data) as $key)
		{
			oci_bind_by_name($this->statement, ':' . $key, $data[$key]);
			$last_key = $key;
		}

		if ($this->isCollectRowId)
		{
			oci_bind_by_name($this->statement, ':' . (++$last_key), $this->db->rowId, 255);
		}

		return oci_execute($this->statement, $this->db->commitMode);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the result object for the prepared query.
	 *
	 * @return mixed
	 */
	public function _getResult()
	{
		return $this->statement;
	}

	//--------------------------------------------------------------------

	/**
	 * Replaces the ? placeholders with :1, :2, etc parameters for use
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

		$sql = preg_replace_callback('/\?/', function ($matches) use (&$count) {
			return ':' . ($count++);
		}, $sql);

		return $sql;
	}
}
