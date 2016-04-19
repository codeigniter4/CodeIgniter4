<?php namespace CodeIgniter\Database;

interface ResultInterface
{
	/**
	 * Retrieve the results of the query. Typically an array of
	 * individual data rows, which can be either an 'array', an
	 * 'object', or a custom class name.
	 *
	 * @param string $type The row type. Either 'array', 'object', or a class name to use
	 *
	 * @return mixed
	 */
	public function getResult($type = 'object'): array;

	//--------------------------------------------------------------------

	/**
	 * Returns the results as an array of custom objects.
	 *
	 * @param string $className  The name of the class to use.
	 *
	 * @return mixed
	 */
	public function getCustomResultObject(string $className);

	//--------------------------------------------------------------------

	/**
	 * Returns the results as an array of arrays.
	 *
	 * If no results, an empty array is returned.
	 *
	 * @return array
	 */
	public function getResultArray(): array;

	//--------------------------------------------------------------------

	/**
	 * Returns the results as an array of objects.
	 *
	 * If no results, an empty array is returned.
	 *
	 * @return array
	 */
	public function getResultObject(): array;

	//--------------------------------------------------------------------

	/**
	 * Wrapper object to return a row as either an array, an object, or
	 * a custom class.
	 *
	 * If row doesn't exist, returns null.
	 *
	 * @param int    $n     The index of the results to return
	 * @param string $type  The type of result object. 'array', 'object' or class name.
	 *
	 * @return mixed
	 */
	public function getRow($n = 0, $type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Returns a row as a custom class instance.
	 *
	 * If row doesn't exists, returns null.
	 *
	 * @param string $className
	 *
	 * @return mixed
	 */
	public function getCustomRowObject($n, string $className);

	//--------------------------------------------------------------------

	/**
	 * Returns a single row from the results as an array.
	 *
	 * If row doesn't exist, returns null.
	 *
	 * @param int $n
	 *
	 * @return mixed
	 */
	public function getRowArray($n = 0);

	//--------------------------------------------------------------------

	/**
	 * Returns a single row from the results as an object.
	 *
	 * If row doesn't exist, returns null.
	 *
	 * @param int $n
	 *
	 * @return mixed
	 */
	public function getRowObject($n = 0);

	//--------------------------------------------------------------------

	/**
	 * Assigns an item into a particular column slot.
	 *
	 * @param      $key
	 * @param null $value
	 *
	 * @return mixed
	 */
	public function setRow($key, $value = null);

	//--------------------------------------------------------------------

	/**
	 * Returns the "first" row of the current results.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getFirstRow($type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Returns the "last" row of the current results.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getLastRow($type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Returns the "next" row of the current results.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getNextRow($type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Returns the "previous" row of the current results.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getPreviousRow($type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Returns an unbuffered row and move the pointer to the next row.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getUnbufferedRow($type = 'object');

	//--------------------------------------------------------------------

	/**
	 * Gets the number of fields in the result set.
	 *
	 * @return int
	 */
	public function getFieldCount(): int;

	//--------------------------------------------------------------------

	/**
	 * Generates an array of column names in the result set.
	 *
	 * @return array
	 */
	public function getFieldNames(): array;

	//--------------------------------------------------------------------

	/**
	 * Generates an array of objects representing field meta-data.
	 *
	 * @return array
	 */
	public function getFieldData(): array;

	//--------------------------------------------------------------------

	/**
	 * Frees the current result.
	 *
	 * @return mixed
	 */
	public function freeResult();

	//--------------------------------------------------------------------

	/**
	 * Moves the internal pointer to the desired offset. This is called
	 * internally before fetching results to make sure the result set
	 * starts at zero.
	 *
	 * @param int $n
	 *
	 * @return mixed
	 */
	public function dataSeek($n = 0);

	//--------------------------------------------------------------------

}
