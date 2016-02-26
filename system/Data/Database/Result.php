<?php namespace CodeIgniter\Data\Database;

abstract class Result extends \CodeIgniter\Data\Result
{
	protected $connection;
	protected $index = 0;
	protected $queryResult;
	protected $rowCount;
	protected $data;

	public function __construct(\CodeIgniter\Data\Database\Connection $connection, $queryResult)
	{
		// parent::__construct();

		$this->connection = $connection;
		$this->queryResult = $queryResult;
	}

	public function getIndex()
	{
		return $this->index;
	}

	public function getRowCount()
	{
		if (is_int($this->rowCount) && $this->rowCount > 0)
		{
			return $this->rowCount;
		}

		$this->rowCount = count($this->data);
		if ($this->rowCount > 0)
		{
			return $this->rowCount;
		}

		return $this->rowCount = count($this->getData());
	}

	public function getData()
	{
		if (isset($this->data))
		{
			return $this->data;
		}

		is_null($this->data) || $this->setIndex(0);
		$this->data = [];
		while ($row = $this->fetch())
		{
			$this->data[] = $row;
		}

		return $this->data;
	}

	public function getRow($index = 0)
	{
		if ( ! empty($this->data) && array_key_exists($index, $this->data))
		{
			return $this->data[$index];
		}

		if ($this->getRowCount() === 0)
		{
			return null;
		}

		if ($index !== $this->index && isset($this->data[$index]))
		{
			$this->index = $index;
		}

		return $this->data[$index];
	}

	public function setIndex($index = 0)
	{
		$this->index = $index;
	}
}
