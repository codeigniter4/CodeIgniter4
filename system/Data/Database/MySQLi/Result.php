<?php namespace CodeIgniter\Data\Database\MySQLi;

class Result extends \CodeIgniter\Data\Database\Result
{
	protected $fieldData;
	protected $fieldNames;

	public function getRowCount()
	{
		return is_int($this->rowCount)
			? $this->rowCount
			: $this->rowCount = $this->queryResult->num_rows;
	}

	public function getFieldCount()
	{
		return $this->queryResult->field_count;
	}

	public function getFieldNames()
	{
		if (isset($this->fieldNames))
		{
			return $this->fieldNames;
		}

		if (isset($this->fieldData))
		{
			$this->fieldNames = [];
			foreach ($this->fieldData as $field)
			{
				$this->fieldNames[] = $field->name;
			}

			return $this->fieldNames;
		}

		$this->fieldNames = [];
		$this->queryResult->field_seek(0);
		while ($field = $this->queryResult->fetch_field())
		{
			$this->fieldNames[] = $field->name;
		}

		return $this->fieldNames;
	}

	public function getFieldData()
	{
		if (isset($this->fieldData))
		{
			return $this->fieldData;
		}

		$this->fieldData = [];
		$resultFields = $this->queryResult->fetch_fields();
		for ($i = 0, $c = count($resultFields); $i < $c; $i++)
		{
			$this->fieldData[$i]              = new stdClass();
			$this->fieldData[$i]->name        = $resultFields[$i]->name;
			$this->fieldData[$i]->type        = $resultFields[$i]->type;
			$this->fieldData[$i]->max_length  = $resultFields[$i]->max_length;
			$this->fieldData[$i]->primary_key = (int) ($resultFields[$i]->flags & 2);
			$this->fieldData[$i]->default     = $resultFields[$i]->def;
		}

		return $this->fieldData;
	}

	public function freeResult()
	{
		if (is_object($this->queryResult))
		{
			$this->queryResult->free();
			$this->queryResult = false;
		}
	}

	public function setIndex($index = 0)
	{
		return $this->queryResult->data_seek($index);
	}

	public function fetch()
	{
		return $this->queryResult->fetch_object('stdClass');
	}

	public function fetchArray()
	{
		return $this->queryResult->fetch_assoc();
	}

	public function fetchAs($className = 'array')
	{
		if (strtolower($className) === 'array')
		{
			return $this->fetchArray();
		}

		$this->queryResult->fetch_object($className);
	}
}
