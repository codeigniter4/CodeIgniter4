<?php namespace CodeIgniter\Database\Postgre;

use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\ResultInterface;

class Result extends BaseResult implements ResultInterface
{
	/**
	 * Gets the number of fields in the result set.
	 *
	 * @return int
	 */
	public function getFieldCount(): int
	{
		return return pg_num_fields($this->resultID);
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
		for ($i = 0, $c = $this->getFieldCount(); $i < $c; $i++)
		{
			$fieldNames[] = pg_field_name($this->resultID, $i);
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
		$retval = [];

		for ($i = 0, $c = $this->getFieldCount(); $i < $c; $i++)
		{
			$retval[$i]              = new \stdClass();
			$retval[$i]->name        = pg_field_name($this->resultID, $i);
			$retval[$i]->type        = pg_field_type($this->resultID, $i);
			$retval[$i]->max_length  = pg_field_size($this->resultID, $i);
			// $retval[$i]->primary_key = (int)($fieldData[$i]->flags & 2);
			// $retval[$i]->default     = $fieldData[$i]->def;
		}

		return $retval;
	}

	//--------------------------------------------------------------------

	/**
	 * Frees the current result.
	 *
	 * @return mixed
	 */
	public function freeResult()
	{
		if (is_resource($this->resultID))
		{
			pg_free_result($this->resultID);
			$this->resultID = false;
		}
	}

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
	public function dataSeek($n = 0)
	{
		return pg_result_seek($this->resultID, $n);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the result set as an array.
	 *
	 * Overridden by driver classes.
	 *
	 * @return array
	 */
	protected function fetchAssoc()
	{
		return pg_fetch_assoc($this->resultID);
	}

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
	protected function fetchObject($className = 'stdClass')
	{
		return pg_fetch_object($this->resultID, null, $className);
	}

	//--------------------------------------------------------------------
}
