<?php namespace CodeIgniter\Test\Mock;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\ConnectionInterface;

class MockBuilder extends BaseBuilder {

	public function __construct($tableName, ConnectionInterface &$db, array $options = null)
	{
		parent::__construct($tableName, $db, $options);
	}

	//--------------------------------------------------------------------

}
