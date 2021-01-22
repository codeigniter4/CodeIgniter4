<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database;

use CodeIgniter\Entity;

/**
 * Class BaseResult
 */
abstract class BaseResult implements ResultInterface
{
	/**
	 * Connection ID
	 *
	 * @var resource|object
	 */
	public $connID;

	/**
	 * Result ID
	 *
	 * @var resource|object|boolean
	 */
	public $resultID;

	/**
	 * Result Array
	 *
	 * @var array[]
	 */
	public $resultArray = [];

	/**
	 * Result Object
	 *
	 * @var object[]
	 */
	public $resultObject = [];

	/**
	 * Custom Result Object
	 *
	 * @var array
	 */
	public $customResultObject = [];

	/**
	 * Current Row index
	 *
	 * @var integer
	 */
	public $currentRow = 0;

	/**
	 * The number of records in the query result
	 *
	 * @var integer|null
	 */
	protected $numRows = null;

	/**
	 * Row data
	 *
	 * @var array|null
	 */
	public $rowData;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param object|resource $connID
	 * @param object|resource $resultID
	 */
	public function __construct(&$connID, &$resultID)
	{
		$this->connID   = $connID;
		$this->resultID = $resultID;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the results of the query. Typically an array of
	 * individual data rows, which can be either an 'array', an
	 * 'object', or a custom class name.
	 *
	 * @param string $type The row type. Either 'array', 'object', or a class name to use
	 *
	 * @return array
	 */
	public function getResult(string $type = 'object'): array
	{
		if ($type === 'array')
		{
			return $this->getResultArray();
		}

		if ($type === 'object')
		{
			return $this->getResultObject();
		}

		return $this->getCustomResultObject($type);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the results as an array of custom objects.
	 *
	 * @param string $className The name of the class to use.
	 *
	 * @return mixed
	 */
	public function getCustomResultObject(string $className)
	{
		if (isset($this->customResultObject[$className]))
		{
			return $this->customResultObject[$className];
		}

		if (is_bool($this->resultID) || ! $this->resultID)
		{
			return [];
		}

		// Don't fetch the result set again if we already have it
		$_data = null;
		if (($c = count($this->resultArray)) > 0)
		{
			$_data = 'resultArray';
		}
		elseif (($c = count($this->resultObject)) > 0)
		{
			$_data = 'resultObject';
		}

		if ($_data !== null)
		{
			for ($i = 0; $i < $c; $i ++)
			{
				$this->customResultObject[$className][$i] = new $className();

				foreach ($this->{$_data}[$i] as $key => $value)
				{
					$this->customResultObject[$className][$i]->$key = $value;
				}
			}

			return $this->customResultObject[$className];
		}

		is_null($this->rowData) || $this->dataSeek();
		$this->customResultObject[$className] = [];

		while ($row = $this->fetchObject($className))
		{
			if (! is_subclass_of($row, Entity::class) && method_exists($row, 'syncOriginal'))
			{
				$row->syncOriginal();
			}

			$this->customResultObject[$className][] = $row;
		}

		// @phpstan-ignore-next-line
		return $this->customResultObject[$className];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the results as an array of arrays.
	 *
	 * If no results, an empty array is returned.
	 *
	 * @return array
	 */
	public function getResultArray(): array
	{
		if (! empty($this->resultArray))
		{
			return $this->resultArray;
		}

		// In the event that query caching is on, the result_id variable
		// will not be a valid resource so we'll simply return an empty
		// array.
		if (is_bool($this->resultID) || ! $this->resultID)
		{
			return [];
		}

		if ($this->resultObject)
		{
			foreach ($this->resultObject as $row)
			{
				$this->resultArray[] = (array) $row;
			}

			return $this->resultArray;
		}

		is_null($this->rowData) || $this->dataSeek();
		while ($row = $this->fetchAssoc())
		{
			$this->resultArray[] = $row;
		}

		return $this->resultArray;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the results as an array of objects.
	 *
	 * If no results, an empty array is returned.
	 *
	 * @return array
	 */
	public function getResultObject(): array
	{
		if (! empty($this->resultObject))
		{
			return $this->resultObject;
		}

		// In the event that query caching is on, the result_id variable
		// will not be a valid resource so we'll simply return an empty
		// array.
		if (is_bool($this->resultID) || ! $this->resultID)
		{
			return [];
		}

		if ($this->resultArray)
		{
			foreach ($this->resultArray as $row)
			{
				$this->resultObject[] = (object) $row;
			}

			return $this->resultObject;
		}

		is_null($this->rowData) || $this->dataSeek();
		while ($row = $this->fetchObject())
		{
			if (! is_subclass_of($row, Entity::class) && method_exists($row, 'syncOriginal'))
			{
				$row->syncOriginal();
			}

			$this->resultObject[] = $row;
		}

		// @phpstan-ignore-next-line
		return $this->resultObject;
	}

	//--------------------------------------------------------------------

	/**
	 * Wrapper object to return a row as either an array, an object, or
	 * a custom class.
	 *
	 * If row doesn't exist, returns null.
	 *
	 * @param mixed  $n    The index of the results to return
	 * @param string $type The type of result object. 'array', 'object' or class name.
	 *
	 * @return mixed
	 */
	public function getRow($n = 0, string $type = 'object')
	{
		if (! is_numeric($n))
		{
			// We cache the row data for subsequent uses
			is_array($this->rowData) || $this->rowData = $this->getRowArray();

			// array_key_exists() instead of isset() to allow for NULL values
			if (empty($this->rowData) || ! array_key_exists($n, $this->rowData))
			{
				return null;
			}

			return $this->rowData[$n];
		}

		if ($type === 'object')
		{
			return $this->getRowObject($n);
		}

		if ($type === 'array')
		{
			return $this->getRowArray($n);
		}

		return $this->getCustomRowObject($n, $type);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a row as a custom class instance.
	 *
	 * If row doesn't exists, returns null.
	 *
	 * @param integer $n
	 * @param string  $className
	 *
	 * @return mixed
	 */
	public function getCustomRowObject(int $n, string $className)
	{
		isset($this->customResultObject[$className]) || $this->getCustomResultObject($className);

		if (empty($this->customResultObject[$className]))
		{
			return null;
		}

		if ($n !== $this->currentRow && isset($this->customResultObject[$className][$n]))
		{
			$this->currentRow = $n;
		}

		return $this->customResultObject[$className][$this->currentRow];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a single row from the results as an array.
	 *
	 * If row doesn't exist, returns null.
	 *
	 * @param integer $n
	 *
	 * @return mixed
	 */
	public function getRowArray(int $n = 0)
	{
		$result = $this->getResultArray();
		if (empty($result))
		{
			return null;
		}

		if ($n !== $this->currentRow && isset($result[$n]))
		{
			$this->currentRow = $n;
		}

		return $result[$this->currentRow];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a single row from the results as an object.
	 *
	 * If row doesn't exist, returns null.
	 *
	 * @param integer $n
	 *
	 * @return mixed
	 */
	public function getRowObject(int $n = 0)
	{
		$result = $this->getResultObject();
		if (empty($result))
		{
			return null;
		}

		if ($n !== $this->customResultObject && isset($result[$n]))
		{
			$this->currentRow = $n;
		}

		return $result[$this->currentRow];
	}

	//--------------------------------------------------------------------

	/**
	 * Assigns an item into a particular column slot.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function setRow($key, $value = null)
	{
		// We cache the row data for subsequent uses
		if (! is_array($this->rowData))
		{
			$this->rowData = $this->getRowArray();
		}

		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->rowData[$k] = $v;
			}

			return;
		}

		if ($key !== '' && $value !== null)
		{
			$this->rowData[$key] = $value;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the "first" row of the current results.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getFirstRow(string $type = 'object')
	{
		$result = $this->getResult($type);

		return (empty($result)) ? null : $result[0];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the "last" row of the current results.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getLastRow(string $type = 'object')
	{
		$result = $this->getResult($type);

		return (empty($result)) ? null : $result[count($result) - 1];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the "next" row of the current results.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getNextRow(string $type = 'object')
	{
		$result = $this->getResult($type);
		if (empty($result))
		{
			return null;
		}

		return isset($result[$this->currentRow + 1]) ? $result[++ $this->currentRow] : null;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the "previous" row of the current results.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getPreviousRow(string $type = 'object')
	{
		$result = $this->getResult($type);
		if (empty($result))
		{
			return null;
		}

		if (isset($result[$this->currentRow - 1]))
		{
			-- $this->currentRow;
		}

		return $result[$this->currentRow];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an unbuffered row and move the pointer to the next row.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function getUnbufferedRow(string $type = 'object')
	{
		if ($type === 'array')
		{
			return $this->fetchAssoc();
		}

		if ($type === 'object')
		{
			return $this->fetchObject();
		}

		return $this->fetchObject($type);
	}

	//--------------------------------------------------------------------

	/**
	 * Number of rows in the result set; checks for previous count, falls
	 * back on counting resultArray or resultObject, finally fetching resultArray
	 * if nothing was previously fetched
	 *
	 * @return integer
	 */
	public function getNumRows(): int
	{
		if (is_int($this->numRows))
		{
			return $this->numRows;
		}
		if ($this->resultArray !== [])
		{
			return $this->numRows = count($this->resultArray);
		}
		if ($this->resultObject !== [])
		{
			return $this->numRows = count($this->resultObject);
		}

		return $this->numRows = count($this->getResultArray());
	}

	/**
	 * Gets the number of fields in the result set.
	 *
	 * @return integer
	 */
	abstract public function getFieldCount(): int;

	//--------------------------------------------------------------------

	/**
	 * Generates an array of column names in the result set.
	 *
	 * @return array
	 */
	abstract public function getFieldNames(): array;

	//--------------------------------------------------------------------

	/**
	 * Generates an array of objects representing field meta-data.
	 *
	 * @return array
	 */
	abstract public function getFieldData(): array;

	//--------------------------------------------------------------------

	/**
	 * Frees the current result.
	 *
	 * @return void
	 */
	abstract public function freeResult();

	//--------------------------------------------------------------------

	/**
	 * Moves the internal pointer to the desired offset. This is called
	 * internally before fetching results to make sure the result set
	 * starts at zero.
	 *
	 * @param integer $n
	 *
	 * @return mixed
	 */
	abstract public function dataSeek(int $n = 0);

	//--------------------------------------------------------------------

	/**
	 * Returns the result set as an array.
	 *
	 * Overridden by driver classes.
	 *
	 * @return mixed
	 */
	abstract protected function fetchAssoc();

	//--------------------------------------------------------------------

	/**
	 * Returns the result set as an object.
	 *
	 * Overridden by child classes.
	 *
	 * @param string $className
	 *
	 * @return object
	 */
	abstract protected function fetchObject(string $className = 'stdClass');

	//--------------------------------------------------------------------
}
