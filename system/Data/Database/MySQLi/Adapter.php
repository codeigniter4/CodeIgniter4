<?php namespace CodeIgniter\Data\Database\MySQLi;

class Adapter extends \CodeIgniter\Data\Database\Adapter
{
	public function __construct(\CodeIgniter\Data\Database\Connection $connection)
	{
		parent::__construct($connection);
	}

	public function dbQuery($sql)
	{
		return $this->connectionId->query($this->prepQuery($sql));
	}

	protected function prepQuery($sql)
	{
		// mysqli_affected_rows() returns 0 for "DELETE FROM TABLE" queries. This
		// modifies the query so a proper number of affected rows is returned.
		if ($this->connectionConfig->deleteHack === true && preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql))
		{
			return trim($sql).' WHERE 1=1';
		}

		return $sql;
	}

	protected function setDBCharacterSet($charset)
	{
		return $this->connectionId->set_charset($charset);
	}
}
