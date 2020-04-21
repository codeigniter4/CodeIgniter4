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

namespace CodeIgniter\Database;

/**
 * Query builder
 *
 * @package CodeIgniter\Database
 */
class Query implements QueryInterface
{

	/**
	 * The query string, as provided by the user.
	 *
	 * @var string
	 */
	protected $originalQueryString;

	/**
	 * The final query string after binding, etc.
	 *
	 * @var string
	 */
	protected $finalQueryString;

	/**
	 * The binds and their values used for binding.
	 *
	 * @var array
	 */
	protected $binds = [];

	/**
	 * Bind marker
	 *
	 * Character used to identify values in a prepared statement.
	 *
	 * @var string
	 */
	protected $bindMarker = '?';

	/**
	 * The start time in seconds with microseconds
	 * for when this query was executed.
	 *
	 * @var float
	 */
	protected $startTime;

	/**
	 * The end time in seconds with microseconds
	 * for when this query was executed.
	 *
	 * @var float
	 */
	protected $endTime;

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
	 * Pointer to database connection.
	 * Mainly for escaping features.
	 *
	 * @var BaseConnection
	 */
	public $db;

	//--------------------------------------------------------------------

	/**
	 * BaseQuery constructor.
	 *
	 * @param $db ConnectionInterface
	 */
	public function __construct(&$db)
	{
		$this->db = $db;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the raw query string to use for this statement.
	 *
	 * @param string  $sql
	 * @param mixed   $binds
	 * @param boolean $setEscape
	 *
	 * @return $this
	 */
	public function setQuery(string $sql, $binds = null, bool $setEscape = true)
	{
		$this->originalQueryString = $sql;

		if (! is_null($binds))
		{
			if (! is_array($binds))
			{
				$binds = [$binds];
			}

			if ($setEscape)
			{
				array_walk($binds, function (&$item) {
					$item = [
						$item,
						true,
					];
				});
			}
			$this->binds = $binds;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Will store the variables to bind into the query later.
	 *
	 * @param array $binds
	 *
	 * @return $this
	 */
	public function setBinds(array $binds)
	{
		$this->binds = $binds;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the final, processed query string after binding, etal
	 * has been performed.
	 *
	 * @return string
	 */
	public function getQuery(): string
	{
		if (empty($this->finalQueryString))
		{
			$this->finalQueryString = $this->originalQueryString;
		}

		$this->compileBinds();

		return $this->finalQueryString;
	}

	//--------------------------------------------------------------------

	/**
	 * Records the execution time of the statement using microtime(true)
	 * for it's start and end values. If no end value is present, will
	 * use the current time to determine total duration.
	 *
	 * @param float $start
	 * @param float $end
	 *
	 * @return $this
	 */
	public function setDuration(float $start, float $end = null)
	{
		$this->startTime = $start;

		if (is_null($end))
		{
			$end = microtime(true);
		}

		$this->endTime = $end;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the start time in seconds with microseconds.
	 *
	 * @param boolean $returnRaw
	 * @param integer $decimals
	 *
	 * @return string
	 */
	public function getStartTime(bool $returnRaw = false, int $decimals = 6): string
	{
		if ($returnRaw)
		{
			return $this->startTime;
		}

		return number_format($this->startTime, $decimals);
	}

	//--------------------------------------------------------------------
	/**
	 * Returns the duration of this query during execution, or null if
	 * the query has not been executed yet.
	 *
	 * @param integer $decimals The accuracy of the returned time.
	 *
	 * @return string
	 */
	public function getDuration(int $decimals = 6): string
	{
		return number_format(($this->endTime - $this->startTime), $decimals);
	}

	//--------------------------------------------------------------------

	/**
	 * Stores the error description that happened for this query.
	 *
	 * @param integer $code
	 * @param string  $error
	 *
	 * @return $this
	 */
	public function setError(int $code, string $error)
	{
		$this->errorCode   = $code;
		$this->errorString = $error;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Reports whether this statement created an error not.
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

	/**
	 * Determines if the statement is a write-type query or not.
	 *
	 * @return boolean
	 */
	public function isWriteType(): bool
	{
		return (bool) preg_match(
						'/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD|COPY|ALTER|RENAME|GRANT|REVOKE|LOCK|UNLOCK|REINDEX)\s/i', $this->originalQueryString);
	}

	//--------------------------------------------------------------------

	/**
	 * Swaps out one table prefix for a new one.
	 *
	 * @param string $orig
	 * @param string $swap
	 *
	 * @return $this
	 */
	public function swapPrefix(string $orig, string $swap)
	{
		$sql = empty($this->finalQueryString) ? $this->originalQueryString : $this->finalQueryString;

		$this->finalQueryString = preg_replace('/(\W)' . $orig . '(\S+?)/', '\\1' . $swap . '\\2', $sql);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the original SQL that was passed into the system.
	 *
	 * @return string
	 */
	public function getOriginalQuery(): string
	{
		return $this->originalQueryString;
	}

	//--------------------------------------------------------------------

	/**
	 * Escapes and inserts any binds into the finalQueryString object.
	 *
	 * @return null|void
	 */
	protected function compileBinds()
	{
		$sql = $this->finalQueryString;

		$hasNamedBinds = strpos($sql, ':') !== false && strpos($sql, ':=') === false;

		if (empty($this->binds) || empty($this->bindMarker) ||
				(strpos($sql, $this->bindMarker) === false &&
				$hasNamedBinds === false)
		)
		{
			return;
		}

		if (! is_array($this->binds))
		{
			$binds     = [$this->binds];
			$bindCount = 1;
		}
		else
		{
			$binds     = $this->binds;
			$bindCount = count($binds);
		}

		// Reverse the binds so that duplicate named binds
		// will be processed prior to the original binds.
		if (! is_numeric(key(array_slice($binds, 0, 1))))
		{
			$binds = array_reverse($binds);
		}

		// We'll need marker length later
		$ml = strlen($this->bindMarker);

		if ($hasNamedBinds)
		{
			$sql = $this->matchNamedBinds($sql, $binds);
		}
		else
		{
			$sql = $this->matchSimpleBinds($sql, $binds, $bindCount, $ml);
		}

		$this->finalQueryString = $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Match bindings
	 *
	 * @param  string $sql
	 * @param  array  $binds
	 * @return string
	 */
	protected function matchNamedBinds(string $sql, array $binds): string
	{
		$replacers = [];

		foreach ($binds as $placeholder => $value)
		{
			// $value[1] contains the boolean whether should be escaped or not
			$escapedValue = $value[1] ? $this->db->escape($value[0]) : $value[0];

			// In order to correctly handle backlashes in saved strings
			// we will need to preg_quote, so remove the wrapping escape characters
			// otherwise it will get escaped.
			if (is_array($value[0]))
			{
				$escapedValue = '(' . implode(',', $escapedValue) . ')';
			}

			$replacers[":{$placeholder}:"] = $escapedValue;
		}

		return strtr($sql, $replacers);
	}

	//--------------------------------------------------------------------

	/**
	 * Match bindings
	 *
	 * @param  string  $sql
	 * @param  array   $binds
	 * @param  integer $bindCount
	 * @param  integer $ml
	 * @return string
	 */
	protected function matchSimpleBinds(string $sql, array $binds, int $bindCount, int $ml): string
	{
		// Make sure not to replace a chunk inside a string that happens to match the bind marker
		if ($c = preg_match_all("/'[^']*'/i", $sql, $matches))
		{
			$c = preg_match_all('/' . preg_quote($this->bindMarker, '/') . '/i', str_replace($matches[0], str_replace($this->bindMarker, str_repeat(' ', $ml), $matches[0]), $sql, $c), $matches, PREG_OFFSET_CAPTURE);

			// Bind values' count must match the count of markers in the query
			if ($bindCount !== $c)
			{
				return $sql;
			}
		}
		// Number of binds must match bindMarkers in the string.
		else if (($c = preg_match_all('/' . preg_quote($this->bindMarker, '/') . '/i', $sql, $matches, PREG_OFFSET_CAPTURE)) !== $bindCount)
		{
			return $sql;
		}

		do
		{
			$c--;
			$escapedValue = $binds[$c][1] ? $this->db->escape($binds[$c][0]) : $binds[$c][0];
			if (is_array($escapedValue))
			{
				$escapedValue = '(' . implode(',', $escapedValue) . ')';
			}
			$sql = substr_replace($sql, $escapedValue, $matches[0][$c][1], $ml);
		}
		while ($c !== 0);

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Return text representation of the query
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->getQuery();
	}

	//--------------------------------------------------------------------
}
