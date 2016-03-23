<?php namespace CodeIgniter\Database;

class BaseQuery implements QueryInterface
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
	 * @var    string
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
	 * Identifier escape character
	 *
	 * @var    string
	 */
	protected $escapeChar = '"';

	/**
	 * ESCAPE statement string
	 *
	 * @var    string
	 */
	protected $likeEscapeStr = " ESCAPE '%s' ";

	/**
	 * ESCAPE character
	 *
	 * @var    string
	 */
	protected $likeEscapeChar = '!';

	/**
	 * ORDER BY random keyword
	 *
	 * @var    array
	 */
	protected $randomKeyword = ['RAND()', 'RAND(%d)'];

	/**
	 * COUNT string
	 *
	 * @used-by    CI_DB_driver::count_all()
	 * @used-by    CI_DB_query_builder::count_all_results()
	 *
	 * @var    string
	 */
	protected $countString = 'SELECT COUNT(*) AS ';

	//--------------------------------------------------------------------

	/**
	 * Sets the raw query string to use for this statement.
	 *
	 * @param string $sql
	 *
	 * @return mixed
	 */
	public function setQuery(string $sql, $binds=null)
	{
		$this->originalQueryString = $sql;

		if (! is_null($binds))
		{
			$this->binds = $binds;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the final, processed query string after binding, etal
	 * has been performed.
	 *
	 * @return mixed
	 */
	public function getQuery()
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
	 * @param int      $start
	 * @param int|null $end
	 *
	 * @return mixed
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
	 * Returns the duration of this query during execution, or null if
	 * the query has not been executed yet.
	 *
	 * @param int $decimals The accuracy of the returned time.
	 *
	 * @return mixed
	 */
	public function getDuration(int $decimals = 6)
	{
		return number_format(($this->endTime - $this->startTime), $decimals);
	}

	//--------------------------------------------------------------------

	/**
	 * Stores the error description that happened for this query.
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
	 * @return bool
	 */
	public function hasError(): bool
	{
		return ! empty($this->errorString);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the error code created while executing this statement.
	 *
	 * @return string
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
	 * @return bool
	 */
	public function isWriteType(): bool
	{
		return (bool)preg_match(
			'/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD|COPY|ALTER|RENAME|GRANT|REVOKE|LOCK|UNLOCK|REINDEX)\s/i',
			$this->originalQueryString);
	}

	//--------------------------------------------------------------------

	/**
	 * Swaps out one table prefix for a new one.
	 *
	 * @param string $orig
	 * @param string $swap
	 *
	 * @return mixed
	 */
	public function swapPrefix(string $orig, string $swap)
	{
		$sql = empty($this->finalQueryString) ? $this->originalQueryString : $this->finalQueryString;

		$this->finalQueryString = preg_replace('/(\W)'.$orig.'(\S+?)/', '\\1'.$swap.'\\2', $sql);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Escapes and inserts any binds into the finalQueryString object.
	 */
	protected function compileBinds()
	{
		if (empty($this->binds) || empty($this->bindMarker) ||
		    strpos($this->finalQueryString, $this->bindMarker) === false
		)
		{
			return;
		}

		if ( ! is_array($this->binds))
		{
			$binds     = [$this->binds];
			$bindCount = 1;
		}
		else
		{
			// Make sure we're using numeric keys
			$binds     = array_values($this->binds);
			$bindCount = count($binds);
		}

		// We'll need marker length later
		$ml = strlen($this->bindMarker);

		$sql = $this->finalQueryString;

		// Make sure not to replace a chunk inside a string that happens to match the bind marker
		if ($c = preg_match_all("/'[^']*'/i", $sql, $matches))
		{
			$c = preg_match_all('/'.preg_quote($this->bindMarker, '/').'/i',
				str_replace($matches[0],
					str_replace($this->bindMarker, str_repeat(' ', $ml), $matches[0]),
					$sql, $c),
				$matches, PREG_OFFSET_CAPTURE);

			// Bind values' count must match the count of markers in the query
			if ($bindCount !== $c)
			{
				return;
			}
		}
		// Number of binds must match bindMarkers in the string.
		else if (($c = preg_match_all('/'.preg_quote($this->bindMarker, '/').'/i', $sql, $matches,
				PREG_OFFSET_CAPTURE)) !== $bindCount
		)
		{
			return;
		}

		do
		{
			$c--;
			$escaped_value = $this->escape($binds[$c]);
			if (is_array($escaped_value))
			{
				$escaped_value = '('.implode(',', $escaped_value).')';
			}
			$sql = substr_replace($sql, $escaped_value, $matches[0][$c][1], $ml);
		}
		while ($c !== 0);

		$this->finalQueryString = $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * "Smart" Escape String
	 *
	 * Escapes data based on type.
	 * Sets boolean and null types
	 *
	 * @param $str
	 *
	 * @return mixed
	 */
	public function escape($str)
	{
		if (is_array($str))
		{
			$str = array_map([&$this, 'escape'], $str);

			return $str;
		}
		else if (is_string($str) OR (is_object($str) && method_exists($str, '__toString')))
		{
			return "'".$this->escapeString($str)."'";
		}
		else if (is_bool($str))
		{
			return ($str === false) ? 0 : 1;
		}
		else if ($str === null)
		{
			return 'NULL';
		}

		return $str;
	}

	//--------------------------------------------------------------------

	/**
	 * Escape String
	 *
	 * @param	string|string[]	$str	Input string
	 * @param	bool	$like	Whether or not the string will be used in a LIKE condition
	 * @return	string
	 */
	protected function escapeString($str, $like = FALSE)
	{
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = $this->escapeString($val, $like);
			}

			return $str;
		}

		$str = $this->_escapeString($str);

		// escape LIKE condition wildcards
		if ($like === true)
		{
			return str_replace(
				[$this->likeEscapeChar, '%', '_'],
				[$this->likeEscapeChar.$this->likeEscapeChar, $this->likeEscapeChar.'%', $this->likeEscapeChar.'_'],
				$str
			);
		}

		return $str;
	}

	//--------------------------------------------------------------------

	/**
	 * Platform independent string escape.
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	protected function _escapeString(string $str): string
	{
		return str_replace("'", "''", remove_invisible_characters($str));
	}

	//--------------------------------------------------------------------

}
