<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database\SQLite3;

use Exception;
use Closure;
use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Entity;
use stdClass;

/**
 * Result for SQLite3
 */
class Result extends BaseResult
{

	/**
	 * SQLite3 doesn't have a numRows method or property so we store brute-force counting here
	 *
	 * @var null|integer
	 */
	protected $numRows;

	/**
	 * Gets the number of fields in the result set.
	 *
	 * @return integer
	 */
	public function getFieldCount(): int
	{
		return $this->resultID->numColumns(); // @phpstan-ignore-line
	}

	//--------------------------------------------------------------------

	/**
	 * Generates an array of column names in the result set.
	 *
	 * @return array
	 */
	public function getFieldNames(): array
	{
		$fieldNames = [];
		for ($i = 0, $c = $this->getFieldCount(); $i < $c; $i ++)
		{
			$fieldNames[] = $this->resultID->columnName($i); // @phpstan-ignore-line
		}

		return $fieldNames;
	}

	//--------------------------------------------------------------------

	/**
	 * Generates an array of objects representing field meta-data.
	 *
	 * @return array
	 */
	public function getFieldData(): array
	{
		static $dataTypes = [
			SQLITE3_INTEGER => 'integer',
			SQLITE3_FLOAT   => 'float',
			SQLITE3_TEXT    => 'text',
			SQLITE3_BLOB    => 'blob',
			SQLITE3_NULL    => 'null',
		];

		$retVal = [];
		$this->resultID->fetchArray(SQLITE3_NUM); // @phpstan-ignore-line

		for ($i = 0, $c = $this->getFieldCount(); $i < $c; $i ++)
		{
			$retVal[$i]             = new stdClass();
			$retVal[$i]->name       = $this->resultID->columnName($i); // @phpstan-ignore-line
			$type                   = $this->resultID->columnType($i); // @phpstan-ignore-line
			$retVal[$i]->type       = $type;
			$retVal[$i]->type_name  = isset($dataTypes[$type]) ? $dataTypes[$type] : null;
			$retVal[$i]->max_length = null;
			$retVal[$i]->length     = null;
		}
		$this->resultID->reset(); // @phpstan-ignore-line

		return $retVal;
	}

	//--------------------------------------------------------------------

	/**
	 * Frees the current result.
	 *
	 * @return void
	 */
	public function freeResult()
	{
		if (is_object($this->resultID))
		{
			$this->resultID->finalize();
			$this->resultID = false;
			$this->numRows  = null;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Moves the internal pointer to the desired offset. This is called
	 * internally before fetching results to make sure the result set
	 * starts at zero.
	 *
	 * @param integer $n
	 *
	 * @return mixed
	 * @throws DatabaseException
	 */
	public function dataSeek(int $n = 0)
	{
		if ($n !== 0)
		{
			throw new DatabaseException('SQLite3 doesn\'t support seeking to other offset.');
		}

		return $this->resultID->reset(); // @phpstan-ignore-line
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the result set as an array.
	 *
	 * Overridden by driver classes.
	 *
	 * @return mixed
	 */
	protected function fetchAssoc()
	{
		return $this->resultID->fetchArray(SQLITE3_ASSOC); // @phpstan-ignore-line
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the result set as an object.
	 *
	 * Overridden by child classes.
	 *
	 * @param string $className
	 *
	 * @return object|boolean
	 */
	protected function fetchObject(string $className = 'stdClass')
	{
		// No native support for fetching rows as objects
		if (($row = $this->fetchAssoc()) === false)
		{
			return false;
		}

		if ($className === 'stdClass')
		{
			return (object) $row;
		}

		$classObj = new $className();

		if (is_subclass_of($className, Entity::class))
		{
			return $classObj->setAttributes($row);
		}

		$classSet = Closure::bind(function ($key, $value) {
			$this->$key = $value;
		}, $classObj, $className
		);
		foreach (array_keys($row) as $key)
		{
			$classSet($key, $row[$key]);
		}
		return $classObj;
	}

	//--------------------------------------------------------------------
	/**
	 * SQLite3Result class does not have a numRows function, so we have to brute force count results
	 * NOTE: brute force counting the results also resets the results, which might cause problems.
	 *
	 * @throws DatabaseException
	 */
	public function getNumRows() : int
	{
		if (! $this->resultID)
		{
			throw new DatabaseException(__METHOD__ . ' cannot run if there is no query result');
		}
		if (is_null($this->numRows))
		{
			// the rows have not been counted yet, count by brute force
			$nrows = 0;
			$this->resultID->reset();
			while ($this->resultID->fetchArray(SQLITE3_NUM)) // SQLITE3_NUM should be slightly more efficient
			{
				$nrows++;
			}
			$this->resultID->reset();
			$this->numRows = $nrows;
		}

		return $this->numRows;
	}

	//--------------------------------------------------------------------
}
