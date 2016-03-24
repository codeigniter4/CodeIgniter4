<?php namespace CodeIgniter\Database;

class MockConnection extends BaseConnection
{
	protected $returnValue;

	public $database;

	//--------------------------------------------------------------------

	/**
	 * Connect to the database.
	 *
	 * @return mixed
	 */
	public function connect($persistant = false)
	{
		// ?
		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Keep or establish the connection if no queries have been sent for
	 * a length of time exceeding the server's idle timeout.
	 *
	 * @return mixed
	 */
	 public function reconnect()
	 {
		return true;
	 }

	//--------------------------------------------------------------------

	/**
	 * Select a specific database table to use.
	 *
	 * @param string $databaseName
	 *
	 * @return mixed
	 */
	public function setDatabase(string $databaseName)
	{
		$this->database = $databaseName;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a string containing the version of the database being used.
	 *
	 * @return mixed
	 */
	public function getVersion()
	{
		return CI_VERSION;
	}

	//--------------------------------------------------------------------

	/**
	 * Executes the query against the database.
	 *
	 * @param $sql
	 *
	 * @return mixed
	 */
	protected function execute($sql)
	{
		return $this->returnValue;
	}

	//--------------------------------------------------------------------

	public function shouldReturn($return)
	{
	    $this->returnValue = $return;
	}

	//--------------------------------------------------------------------


}
