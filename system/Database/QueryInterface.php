<?php namespace CodeIgniter\Database;

/**
 * Interface StatementInterface
 *
 * Represents a single statement that can be executed against the database.
 * Statements are platform-specific and can handle binding of binds.
 *
 * @package CodeIgniter\Database
 */
interface QueryInterface
{
	/**
	 * Sets the raw query string to use for this statement.
	 *
	 * @param string $sql
	 *
	 * @return mixed
	 */
	public function setQuery(string $sql, $binds = null);

	//--------------------------------------------------------------------

	/**
	 * Returns the final, processed query string after binding, etal
	 * has been performed.
	 *
	 * @return mixed
	 */
	public function getQuery();

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
	public function setDuration(float $start, float $end = null);

	//--------------------------------------------------------------------

	/**
	 * Returns the duration of this query during execution, or null if
	 * the query has not been executed yet.
	 *
	 * @param int    $decimals  The accuracy of the returned time.
	 *
	 * @return mixed
	 */
	public function getDuration(int $decimals = 6);

	//--------------------------------------------------------------------

	/**
	 * Stores the error description that happened for this query.
	 */
	public function setError(int $code, string $error);

	//--------------------------------------------------------------------

	/**
	 * Reports whether this statement created an error not.
	 *
	 * @return bool
	 */
	public function hasError(): bool;

	//--------------------------------------------------------------------

	/**
	 * Returns the error code created while executing this statement.
	 *
	 * @return string
	 */
	public function getErrorCode(): int;

	//--------------------------------------------------------------------

	/**
	 * Returns the error message created while executing this statement.
	 *
	 * @return string
	 */
	public function getErrorMessage(): string;

	//--------------------------------------------------------------------

	/**
	 * Determines if the statement is a write-type query or not.
	 *
	 * @return bool
	 */
	public function isWriteType(): bool;

	//--------------------------------------------------------------------

	/**
	 * Swaps out one table prefix for a new one.
	 *
	 * @param string $orig
	 * @param string $swap
	 *
	 * @return mixed
	 */
	public function swapPrefix(string $orig, string $swap);

	//--------------------------------------------------------------------

}
