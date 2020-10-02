<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
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
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Database\Sqlsrv;

use CodeIgniter\Database\PreparedQueryInterface;
use CodeIgniter\Database\BasePreparedQuery;

/**
 * Prepared query for Postgre
 */
class PreparedQuery extends BasePreparedQuery implements PreparedQueryInterface
{

	/**
	 * Parameters array used to store the dynamic variables.
	 *
	 * @var array
	 */
	protected $parameters = [];

	/**
	 * The result boolean from a sqlsrv_execute.
	 *
	 * @var boolean
	 */
	protected $result;

	//--------------------------------------------------------------------

	/**
	 * Prepares the query against the database, and saves the connection
	 * info necessary to execute the query later.
	 *
	 * NOTE: This version is based on SQL code. Child classes should
	 * override this method.
	 *
	 * @param string $sql
	 * @param array  $options Options takes an associative array;
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function _prepare(string $sql, array $options = [])
	{
		/* Prepare parameters for the query */
		$queryString = $this->getQueryString();

		$parameters = $this->parameterize($queryString);

		/* Prepare  the query */
		$this->statement = sqlsrv_prepare($this->db->connID, $sql, $parameters);

		if (! $this->statement)
		{
			$info              = $this->db->error();
			$this->errorCode   = $info['code'];
			$this->errorString = $info['message'];
		}

		return $this;
	}

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

		foreach ($data as $key => $value)
		{
			$this->parameters[$key] = $value;
		}

		$this->result = sqlsrv_execute($this->statement);

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
	 * Handle parameters
	 *
	 * @param string $queryString
	 *
	 * @return array
	 */
	protected function parameterize(string $queryString): array
	{
		$numberOfVariables = substr_count($queryString, '?');

		$params = [];

		for ($c = 0; $c < $numberOfVariables; $c++)
		{
			$this->parameters[$c] = null;
			$params[]             = &$this->parameters[$c];
		}

		return $params;
	}

}
