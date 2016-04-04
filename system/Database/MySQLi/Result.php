<?php namespace CodeIgniter\Database\MySQLi;

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
		return $this->resultID->field_count;
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
		$this->resultID->field_seek(0);
		while ($field = $this->resultID->fetch_field())
		{
			$fieldNames[] = $field->name;
		}

		return $fieldNames;
	}

	//--------------------------------------------------------------------

	/**
	 * Generates an array of objects representing field meta-data.
	 *
	 * @return array
	 */
	public function getFieldMeta(): array
	{
		$retval    = [];
		$fieldData = $this->resultID->fetch_fields();

		for ($i = 0, $c = count($fieldData); $i < $c; $i++)
		{
			$retval[$i]              = new \stdClass();
			$retval[$i]->name        = $fieldData[$i]->name;
			$retval[$i]->type        = $fieldData[$i]->type;
			$retval[$i]->max_length  = $fieldData[$i]->max_length;
			$retval[$i]->primary_key = (int)($fieldData[$i]->flags & 2);
			$retval[$i]->default     = $fieldData[$i]->def;
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
		if (is_object($this->resultID))
		{
			$this->resultID->free();
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
		return $this->resultID->data_seek($n);
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
		return $this->resultID->fetch_assoc();
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
		return $this->resultID->fetch_object($className);
	}

	//--------------------------------------------------------------------
}
