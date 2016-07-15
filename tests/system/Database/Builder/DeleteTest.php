<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\MockConnection;

class DeleteTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	public function setUp()
	{
		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testDelete()
	{
		$builder = $this->db->table('jobs');

		$answer = $builder->delete(['id' => 1], null, true, true);

		$expectedSQL   = "DELETE FROM \"jobs\" WHERE \"id\" = :id";
		$expectedBinds = ['id' => 1];

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $answer));
		$this->assertEquals($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------
}
