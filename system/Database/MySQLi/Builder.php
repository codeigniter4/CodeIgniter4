<?php namespace CodeIgniter\Database\MySQLi;

use CodeIgniter\Database\BuilderTrait;

class Builder extends Query
{
	use BuilderTrait;

	//--------------------------------------------------------------------

	public function __construct(string $tableName)
	{
		$this->trackAliases($tableName);
		$this->from($tableName);
	}

	//--------------------------------------------------------------------


}
