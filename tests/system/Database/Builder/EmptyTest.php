<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\MockConnection;

class EmptyTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	public function setUp()
	{
		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testEmptyWithNoTable()
	{
		$builder = new BaseBuilder('jobs', $this->db);
		$builder->returnDeleteSQL = true;

		$answer = $builder->emptyTable(null, true);

		$expectedSQL   = "DELETE FROM \"jobs\"";

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $answer));
	}

	//--------------------------------------------------------------------

	public function testEmptyWithTable()
	{
		$builder = new BaseBuilder('jobs', $this->db);
		$builder->returnDeleteSQL = true;

		$answer = $builder->emptyTable('users', true);

		$expectedSQL   = "DELETE FROM \"users\"";

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $answer));
	}

	//--------------------------------------------------------------------
}
