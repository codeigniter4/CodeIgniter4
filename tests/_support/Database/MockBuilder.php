<?php namespace CodeIgniter\Database;

class MockBuilder extends BaseBuilder {

	public function __construct(string $tableName, ConnectionInterface &$db, array $options = null)
	{
	    parent::__construct($tableName, $db, $options);
	}

	//--------------------------------------------------------------------


}
