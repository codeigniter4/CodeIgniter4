<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\MockConnection;

class CountTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	public function setUp()
	{
		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testCountAll()
	{
		$builder = new BaseBuilder('jobs', $this->db);

		$expectedSQL   = "SELECT COUNT(*) AS \"numrows\" FROM \"jobs\"";

		$this->assertEquals($expectedSQL, $builder->countAll(true));
	}

	//--------------------------------------------------------------------

	public function testCountAllResults()
	{
		$builder = new BaseBuilder('jobs', $this->db);

		$answer = $builder->where('id >', 3)->countAllResults(null, true);

		$expectedSQL   = "SELECT COUNT(*) AS \"numrows\" FROM \"jobs\" WHERE \"id\" > :id";

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $answer));
	}

	//--------------------------------------------------------------------
}
